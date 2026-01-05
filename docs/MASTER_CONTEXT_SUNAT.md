# KIPU ERP: ENCICLOPEDIA TÉCNICA Y MANUAL DE ARQUITECTURA
## Proyecto: Fork Soberano de Akaunting 3.x para Perú (SUNAT)

Este documento es el mapa definitivo del sistema. Contiene la lógica profunda, los parches de emergencia y las decisiones arquitectónicas tomadas para transformar un ERP genérico en una solución de facturación electrónica inmutable.

---

### 1. MAPA DE MODIFICACIONES AL CORE (THE "DIRTY" LIST)
Para que el sistema sea actualizable, el desarrollador debe saber qué archivos de `app/` han sido "intervenidos". Si se actualiza Akaunting, estos archivos deben revisarse con `git diff`:

- **Models:**
    - `app/Models/Document/Document.php`: **El corazón de la seguridad.** Contiene el bloqueo de edición/borrado en `booted()`, los accessors `invoice_number` y `reason_description`, y la relación `referenced_document` (que rompe el filtro `isRecurring` nativo).
    - `app/Models/Document/CreditNote.php` & `DebitNote.php`: Limpiados para que hereden del padre sin sobreescribir lógica SUNAT.
- **Controllers:**
    - `app/Http/Controllers/Sales/Invoices.php`: Modificado el método `show` para cargar `credit_notes` y `debit_notes` (Eager Loading) y el método `edit` para bloqueo por estado.
    - `app/Http/Controllers/Sales/CreditNotes.php` & `DebitNotes.php`: Actualizados para cargar la relación `referenced_document`.
- **ViewComponents (Backend):**
    - `app/Abstracts/View/Components/Documents/Index.php`: Modificado el orden de las pestañas y el resumen (Sent antes que Draft para notas).
    - `app/Http/ViewComposers/Document/ShowInvoiceNumber.php`: Forzado para inyectar el número de factura afectada usando el nuevo accessor.

---

### 2. INGENIERÍA DE BASE DE DATOS (ESQUEMA EXTENDIDO)
Se han realizado cambios estructurales vía Tinker/Migrations manuales:
- **Tabla `currencies`:** Nueva columna `sunat_rate` (decimal 15,4). Almacena el valor "humano" (ej. 3.75).
- **Tabla `email_templates`:** Se añadió la columna `group` (string) que faltaba en la DB pero el código exigía.
- **Data Seeding:** Se restauraron manualmente 12 plantillas base de correo que habían sido borradas.

---

### 3. EL FRONTEND Y EL "INFERNO" DE NODE 22
- **Webpack Fix:** El proyecto NO compila con `node-sass`. Se migró a `sass` (Dart Sass).
- **Vue.js Customization:** El componente `AkauntingContactCard.vue` fue modificado para:
    1. Mostrar el RUC/DNI dinámicamente según el `document_type`.
    2. Eliminar el botón de "Editar Cliente" dentro de facturas.
- **CSS Recovery:** Akaunting NO genera ciertos CSS. Si se ven vistas rotas, los archivos deben copiarse de `node_modules` a `public/css/third_party/` (Dropzone, Quill, Element-UI).

---

### 4. LÓGICA DE NEGOCIO PERUANA (ALGORITMOS)
- **Cálculo de Importe Neto:** 
    - `Total Mostrado = Amount (Inmutable) - Notas de Crédito + Notas de Débito`.
    - Esta lógica vive en `components/documents/index/document.blade.php`.
- **Detección de Anulación:** 
    - Una factura se considera **ANULADA** si su estado es `cancelled` **O** si la suma de sus Notas de Crédito es >= al Total.
- **Tipo de Cambio Inverso:** 
    - `CurrencyObserver` hace: `AkauntingRate = 1 / InputUser`.
    - La vista de totales hace: `DisplayRate = 1 / AkauntingRate`.

---

### 5. INFRAESTRUCTURA DE DESPLIEGUE
- **Dominio:** `https://kipu.peruhub.org`
- **Túnel:** Cloudflare Tunnel (`cloudflared`). No requiere abrir puertos.
- **Sync:** GitHub como repositorio maestro. Los commits deben seguir el prefijo `feat(peru):` o `fix(ux):`.

---

### 6. TIPS PARA LA PRÓXIMA IA / DEVELOPER
- **Caché:** Akaunting es extremadamente dependiente de la caché. Ante cualquier cambio en Traducciones o Vistas, ejecutar: `php artisan view:clear && php artisan cache:clear`.
- **Inmutabilidad:** Nunca habilites el botón "Editar" en una factura enviada. Es un riesgo legal.
- **Traducciones:** Las claves están dispersas entre `general.php`, `invoices.php` y `documents.php`. Si una palabra no cambia, busca en los tres.

---
**ESTADO FINAL DE SESIÓN:** Sistema estable, peruanizado, inmutable y desplegado. Listo para la fase de integración Greenter.