<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte Dashboard - Universidad Mariana</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Helvetica Neue', Arial, sans-serif;
            font-size: 10px;
            color: #333;
            line-height: 1.3;
        }
        .header {
            background: linear-gradient(135deg, #1e3a5f 0%, #2d5a87 100%);
            color: white;
            padding: 20px;
            text-align: center;
            margin-bottom: 15px;
        }
        .header h1 {
            font-size: 20px;
            margin-bottom: 5px;
        }
        .header p {
            font-size: 12px;
            opacity: 0.9;
        }
        .header .date {
            font-size: 9px;
            opacity: 0.8;
            margin-top: 8px;
        }
        .container {
            padding: 0 15px;
        }
        .section {
            margin-bottom: 20px;
            page-break-inside: avoid;
        }
        .section-title {
            font-size: 14px;
            font-weight: bold;
            color: #1e3a5f;
            border-bottom: 2px solid #2d5a87;
            padding-bottom: 5px;
            margin-bottom: 10px;
        }
        .section-subtitle {
            font-size: 12px;
            font-weight: bold;
            color: #555;
            margin: 10px 0 8px 0;
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 10px;
            margin-bottom: 15px;
        }
        .stat-card {
            background: #f0f4f8;
            border-radius: 6px;
            padding: 10px;
            text-align: center;
            border-left: 3px solid #2d5a87;
        }
        .stat-card .value {
            font-size: 22px;
            font-weight: bold;
            color: #1e3a5f;
        }
        .stat-card .label {
            font-size: 9px;
            color: #666;
            margin-top: 3px;
        }
        .two-col {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }
        .three-col {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 10px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
            font-size: 9px;
        }
        th, td {
            padding: 6px 8px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background: #f0f4f8;
            font-weight: bold;
            color: #1e3a5f;
            font-size: 8px;
            text-transform: uppercase;
        }
        td {
            font-size: 9px;
        }
        tr:hover {
            background: #f8f9fa;
        }
        .badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 10px;
            font-size: 8px;
            font-weight: bold;
        }
        .badge-pending { background: #fff3cd; color: #856404; }
        .badge-approved { background: #d4edda; color: #155724; }
        .badge-returned { background: #cce5ff; color: #004085; }
        .badge-rejected { background: #f8d7da; color: #721c24; }
        .badge-reserved { background: #e2e3e5; color: #383d41; }
        .badge-overdue { background: #ff6b6b; color: white; }
        
        .status-grid {
            display: grid;
            grid-template-columns: repeat(5, 1fr);
            gap: 8px;
            margin-bottom: 15px;
        }
        .status-card {
            background: #f0f4f8;
            border-radius: 6px;
            padding: 10px;
            text-align: center;
        }
        .status-card .value {
            font-size: 18px;
            font-weight: bold;
        }
        .status-card .label {
            font-size: 8px;
            color: #666;
            margin-top: 3px;
        }
        .footer {
            text-align: center;
            padding: 15px;
            color: #666;
            font-size: 8px;
            border-top: 1px solid #ddd;
            margin-top: 20px;
        }
        .page-break {
            page-break-before: always;
        }
        .no-data {
            text-align: center;
            color: #999;
            padding: 20px;
            font-style: italic;
        }
        @page {
            margin: 1.5cm;
            size: A4;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Universidad Mariana</h1>
        <p>Sistema de Gestión de Laboratorios</p>
        <div class="date">Reporte generado el: {{ $generatedAt }}</div>
    </div>

    <div class="container">
        <!-- ESTADÍSTICAS GENERALES -->
        <div class="section">
            <div class="section-title">Resumen General</div>
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="value">{{ $stats['products'] }}</div>
                    <div class="label">Productos</div>
                </div>
                <div class="stat-card">
                    <div class="value">{{ $stats['laboratories'] }}</div>
                    <div class="label">Laboratorios</div>
                </div>
                <div class="stat-card">
                    <div class="value">{{ $stats['usersActive'] }}</div>
                    <div class="label">Usuarios Activos</div>
                </div>
                <div class="stat-card">
                    <div class="value">{{ $stats['usersTotal'] }}</div>
                    <div class="label">Total Usuarios</div>
                </div>
            </div>
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="value">{{ $stats['totalBookings'] }}</div>
                    <div class="label">Total Reservas</div>
                </div>
                <div class="stat-card">
                    <div class="value">{{ $stats['totalLoans'] }}</div>
                    <div class="label">Total Préstamos</div>
                </div>
                <div class="stat-card" style="border-left-color: #dc3545;">
                    <div class="value" style="color: #dc3545;">{{ $stats['overdueLoans'] }}</div>
                    <div class="label">Préstamos Vencidos</div>
                </div>
            </div>
        </div>

        <!-- USUARIOS POR ROL -->
        @if(!empty($usersByRole))
        <div class="section">
            <div class="section-title">Usuarios por Rol</div>
            <div class="three-col">
                @foreach($usersByRole as $role => $count)
                <div class="stat-card">
                    <div class="value">{{ $count }}</div>
                    <div class="label">{{ $role }}</div>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        <!-- ESTADO DE RESERVAS Y PRÉSTAMOS -->
        <div class="two-col">
            <div class="section">
                <div class="section-title">Estado de Reservas</div>
                <div class="status-grid" style="grid-template-columns: repeat(3, 1fr);">
                    <div class="status-card">
                        <div class="value" style="color: #ffc107;">{{ $bookingsByStatus['pending'] ?? 0 }}</div>
                        <div class="label">Pendientes</div>
                    </div>
                    <div class="status-card">
                        <div class="value" style="color: #28a745;">{{ $bookingsByStatus['approved'] ?? 0 }}</div>
                        <div class="label">Aprobadas</div>
                    </div>
                    <div class="status-card">
                        <div class="value" style="color: #17a2b8;">{{ $bookingsByStatus['reserved'] ?? 0 }}</div>
                        <div class="label">Reservadas</div>
                    </div>
                    <div class="status-card">
                        <div class="value" style="color: #dc3545;">{{ $bookingsByStatus['rejected'] ?? 0 }}</div>
                        <div class="label">Rechazadas</div>
                    </div>
                    <div class="status-card">
                        <div class="value" style="color: #6c757d;">{{ $bookingsByStatus['cancelled'] ?? 0 }}</div>
                        <div class="label">Canceladas</div>
                    </div>
                </div>
            </div>

            <div class="section">
                <div class="section-title">Estado de Préstamos</div>
                <div class="status-grid" style="grid-template-columns: repeat(3, 1fr);">
                    <div class="status-card">
                        <div class="value" style="color: #ffc107;">{{ $loans['pending'] }}</div>
                        <div class="label">Pendientes</div>
                    </div>
                    <div class="status-card">
                        <div class="value" style="color: #28a745;">{{ $loans['approved'] }}</div>
                        <div class="label">Aprobados</div>
                    </div>
                    <div class="status-card">
                        <div class="value" style="color: #17a2b8;">{{ $loans['returned'] }}</div>
                        <div class="label">Devueltos</div>
                    </div>
                    <div class="status-card">
                        <div class="value" style="color: #dc3545;">{{ $loans['rejected'] }}</div>
                        <div class="label">Rechazados</div>
                    </div>
                    <div class="status-card">
                        <div class="value" style="color: #ff6b6b;">{{ $loans['overdue'] }}</div>
                        <div class="label">Vencidos</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- RESERVAS POR LABORATORIO -->
        <div class="section">
            <div class="section-title">Reservas por Laboratorio</div>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Laboratorio</th>
                        <th>Ubicación</th>
                        <th>Capacidad</th>
                        <th>Total Reservas</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($bookingsByLaboratory as $lab)
                    <tr>
                        <td>{{ $lab['id'] }}</td>
                        <td>{{ $lab['name'] }}</td>
                        <td>{{ $lab['location'] ?? 'N/A' }}</td>
                        <td>{{ $lab['capacity'] ?? 'N/A' }}</td>
                        <td>{{ $lab['total_bookings'] }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- RESERVAS POR TIPO DE PROYECTO -->
        @if(!empty($bookingsByProjectType))
        <div class="section">
            <div class="section-title">Reservas por Tipo de Proyecto</div>
            <div class="three-col">
                @foreach($bookingsByProjectType as $type => $count)
                <div class="stat-card">
                    <div class="value">{{ $count }}</div>
                    <div class="label">{{ $type }}</div>
                </div>
                @endforeach
            </div>
        </div>
        @endif
    </div>

    <!-- PÁGINA 2: PRODUCTOS -->
    <div class="page-break"></div>
    
    <div class="container">
        <!-- PRODUCTOS MÁS SOLICITADOS -->
        @if(!empty($topProductsLoans))
        <div class="section">
            <div class="section-title">Productos Más Solicitados (Préstamos)</div>
            <table>
                <thead>
                    <tr>
                        <th>Producto</th>
                        <th>Cant. Disponible</th>
                        <th>Total Préstamos</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($topProductsLoans as $product)
                    <tr>
                        <td>{{ $product['name'] }}</td>
                        <td>{{ $product['available_quantity'] }}</td>
                        <td>{{ $product['total_loans'] }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif

        <!-- INVENTARIO DE PRODUCTOS -->
        <div class="section">
            <div class="section-title">Inventario de Productos</div>
            <table>
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Marca</th>
                        <th>Modelo</th>
                        <th>Cant.</th>
                        <th>Ubicación</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($products as $product)
                    <tr>
                        <td>{{ $product['name'] }}</td>
                        <td>{{ $product['brand'] ?? 'N/A' }}</td>
                        <td>{{ $product['model'] ?? 'N/A' }}</td>
                        <td>{{ $product['available_quantity'] }}</td>
                        <td>{{ $product['location'] ?? 'N/A' }}</td>
                        <td>
                            @if($product['available_quantity'] > 3)
                                <span class="badge badge-approved">Disponible</span>
                            @elseif($product['available_quantity'] > 0)
                                <span class="badge badge-pending">Limitado</span>
                            @else
                                <span class="badge badge-rejected">Agotado</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- LABORATORIOS -->
        <div class="section">
            <div class="section-title">Laboratorios</div>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Ubicación</th>
                        <th>Capacidad</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($laboratories as $lab)
                    <tr>
                        <td>{{ $lab['id'] }}</td>
                        <td>{{ $lab['name'] }}</td>
                        <td>{{ $lab['location'] ?? 'N/A' }}</td>
                        <td>{{ $lab['capacity'] ?? 'N/A' }}</td>
                        <td>{{ $lab['status'] ?? 'N/A' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- PÁGINA 3: RESERVAS Y PRÉSTAMOS -->
    <div class="page-break"></div>
    
    <div class="container">
        <!-- RESERVAS PENDIENTES -->
        @if(!empty($pendingBookings))
        <div class="section">
            <div class="section-title">Reservas Pendientes</div>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Solicitante</th>
                        <th>Email</th>
                        <th>Tipo Proyecto</th>
                        <th>Fecha Solicitud</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($pendingBookings as $booking)
                    <tr>
                        <td>{{ $booking['id'] }}</td>
                        <td>{{ $booking['name'] }} {{ $booking['last_name'] }}</td>
                        <td>{{ $booking['email'] }}</td>
                        <td>{{ $booking['project_type'] ?? 'N/A' }}</td>
                        <td>{{ $booking['created_at'] }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif

        <!-- ÚLTIMAS RESERVAS -->
        <div class="section">
            <div class="section-title">Últimas Reservas</div>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Solicitante</th>
                        <th>Laboratorio</th>
                        <th>Tipo</th>
                        <th>Estado</th>
                        <th>Fecha Inicio</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($recentBookings as $booking)
                    <tr>
                        <td>{{ $booking['id'] }}</td>
                        <td>{{ $booking['name'] }} {{ $booking['last_name'] }}</td>
                        <td>{{ $booking['laboratory_name'] ?? 'N/A' }}</td>
                        <td>{{ $booking['project_type'] ?? 'N/A' }}</td>
                        <td>
                            <span class="badge badge-{{ $booking['status'] }}">
                                {{ ucfirst($booking['status']) }}
                            </span>
                        </td>
                        <td>{{ $booking['start_at'] ?? 'N/A' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- ÚLTIMOS PRÉSTAMOS -->
        <div class="section">
            <div class="section-title">Últimos Préstamos</div>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Usuario</th>
                        <th>Producto</th>
                        <th>Estado</th>
                        <th>Aprobado</th>
                        <th>Devolución Est.</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($recentLoans as $loan)
                    <tr>
                        <td>{{ $loan['id'] }}</td>
                        <td>{{ $loan['user_name'] }}</td>
                        <td>{{ $loan['product_name'] }}</td>
                        <td>
                            <span class="badge badge-{{ $loan['status'] }}">
                                {{ ucfirst($loan['status']) }}
                            </span>
                        </td>
                        <td>{{ $loan['approved_at'] ?? 'N/A' }}</td>
                        <td>{{ $loan['estimated_return_at'] ?? 'N/A' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

    <div class="footer">
        <p>Universidad Mariana - Sistema de Gestión de Laboratorios</p>
        <p>Este es un documento generado automáticamente</p>
    </div>
</body>
</html>
