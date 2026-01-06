@props([
    'title',
])

<head>
    @stack('head_start')

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>

    <title>{!! $title !!} - @setting('company.name')</title>

    <!-- CSS Inline Completo para PDF A4 -->
    <style type="text/css">
        /* Reset y Base */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, sans-serif !important;
        }
        
        /* Configuracion A4 */
        @page {
            size: A4;
            margin: 10mm 15mm;
        }
        
        @media print {
            body {
                width: 210mm;
                margin: 0 auto;
            }
        }
        
        body {
            font-size: 10px;
            line-height: 1.4;
            color: #333;
            background: #fff;
        }
        
        /* Layout */
        .print-template {
            width: 100%;
            max-width: 100%;
            padding: 0;
        }
        
        .row {
            width: 100%;
            clear: both;
        }
        
        .row::after {
            content: "";
            display: table;
            clear: both;
        }
        
        .col-100 { width: 100%; }
        .col-60 { width: 60%; }
        .col-40 { width: 40%; }
        .col-50 { width: 50%; }
        
        .float-left { float: left; }
        .float-right { float: right; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .text-left { text-align: left; }
        .clearfix::after { content: ""; display: table; clear: both; }
        
        /* Tablas */
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        th, td {
            padding: 4px 6px;
            vertical-align: top;
        }
        
        /* Texto Blanco para Headers */
        .text-white {
            color: #fff !important;
        }
        
        /* Lineas de tabla */
        .lines {
            border: 1px solid #ddd;
        }
        
        .lines th {
            background-color: #55588b;
            color: #fff;
            font-weight: bold;
            padding: 5px;
        }
        
        .lines td {
            border-bottom: 1px solid #eee;
        }
        
        .lines-radius-border {
            border-radius: 4px;
            overflow: hidden;
        }
        
        /* SUNAT Estilos */
        .sunat-box {
            border: 2px solid #000;
            border-radius: 8px;
        }
        
        .sunat-text {
            font-size: 9px;
        }
        
        .sunat-client-box {
            border: 1px solid #ccc;
            border-radius: 4px;
            background: #fafafa;
        }
        
        /* Impresion de colores */
        * {
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }
    </style>

    @stack('css')

    @stack('stylesheet')

    @livewireStyles

    @stack('js')

    @stack('scripts')

    @stack('head_end')
</head>
