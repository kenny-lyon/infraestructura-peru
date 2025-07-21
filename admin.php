<?php
session_start();
if (!isset($_SESSION['admin_email'])) {
    header('Location: login_admin.html');
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Administración - Puertos y Aeropuertos</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" />
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #0a1a2f;
            color: #fff;
            margin: 0;
            min-height: 100vh;
        }
        .admin-bar {
            background: #232946;
            padding: 18px 32px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0 2px 8px rgba(0,0,0,0.12);
        }
        .admin-bar h1 {
            font-size: 1.5rem;
            margin: 0;
            letter-spacing: 1px;
        }
        .logout-btn {
            background: #e63946;
            color: #fff;
            border: none;
            border-radius: 20px;
            padding: 8px 20px;
            font-size: 1rem;
            cursor: pointer;
            transition: background 0.2s;
        }
        .logout-btn:hover {
            background: #b71c1c;
        }
        .tabs {
            display: flex;
            gap: 10px;
            margin: 30px 0 18px 0;
            justify-content: center;
        }
        .tab-btn {
            background: #232946;
            color: #fff;
            border: none;
            border-radius: 18px 18px 0 0;
            padding: 10px 32px;
            font-size: 1.1rem;
            cursor: pointer;
            transition: background 0.2s, color 0.2s;
        }
        .tab-btn.active {
            background: #4a90e2;
            color: #fff;
        }
        .crud-container {
            max-width: 1100px;
            margin: 0 auto;
            background: #232946;
            border-radius: 16px;
            box-shadow: 0 4px 24px rgba(0,0,0,0.18);
            padding: 32px 24px 24px 24px;
        }
        .crud-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 18px;
        }
        .crud-header h2 {
            margin: 0;
            font-size: 1.3rem;
        }
        .add-btn {
            background: #4a90e2;
            color: #fff;
            border: none;
            border-radius: 20px;
            padding: 8px 20px;
            font-size: 1rem;
            cursor: pointer;
            transition: background 0.2s;
        }
        .add-btn:hover {
            background: #357ab8;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            background: #1a1a2e;
            border-radius: 10px;
            overflow: hidden;
        }
        th, td {
            padding: 10px 8px;
            border-bottom: 1px solid #2d2d44;
            text-align: left;
        }
        th {
            background: #232946;
            color: #4a90e2;
            font-weight: 700;
        }
        tr:last-child td {
            border-bottom: none;
        }
        .crud-actions button {
            background: none;
            border: none;
            color: #4a90e2;
            font-size: 1.2rem;
            cursor: pointer;
            margin-right: 8px;
            transition: color 0.2s;
        }
        .crud-actions button:last-child {
            margin-right: 0;
        }
        .crud-actions button:hover {
            color: #e63946;
        }
        .message {
            margin: 18px 0;
            padding: 12px 18px;
            border-radius: 8px;
            font-size: 1.05rem;
            display: none;
        }
        .message.success {
            background: #2ecc71;
            color: #fff;
        }
        .message.error {
            background: #e63946;
            color: #fff;
        }
        /* Modal */
        .modal-bg {
            display: none;
            position: fixed;
            top: 0; left: 0; right: 0; bottom: 0;
            background: rgba(10,20,40,0.85);
            z-index: 1000;
            align-items: center;
            justify-content: center;
        }
        .modal {
            background: #232946;
            border-radius: 12px;
            padding: 32px 28px 24px 28px;
            min-width: 320px;
            max-width: 420px;
            box-shadow: 0 4px 24px rgba(0,0,0,0.22);
            color: #fff;
        }
        .modal h3 {
            margin-top: 0;
            color: #4a90e2;
        }
        .modal form {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }
        .modal input, .modal select, .modal textarea {
            padding: 8px 12px;
            border-radius: 6px;
            border: 1px solid #4a90e2;
            font-size: 1rem;
            outline: none;
            background: #1a1a2e;
            color: #fff;
            resize: vertical;
        }
        .modal textarea {
            min-height: 60px;
        }
        .modal .modal-actions {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            margin-top: 18px;
        }
        .modal .modal-actions button {
            padding: 8px 18px;
            border-radius: 20px;
            border: none;
            font-size: 1rem;
            cursor: pointer;
        }
        .modal .modal-actions .save-btn {
            background: #4a90e2;
            color: #fff;
        }
        .modal .modal-actions .cancel-btn {
            background: #e63946;
            color: #fff;
        }
        @media (max-width: 700px) {
            .crud-container { padding: 10px 2px; }
            .modal { min-width: 90vw; }
        }
        
        /* Dashboard Styles */
        .dashboard-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 24px;
            padding-bottom: 16px;
            border-bottom: 2px solid #4a90e2;
        }
        .dashboard-controls {
            display: flex;
            align-items: center;
            gap: 16px;
        }
        .refresh-btn {
            background: #4a90e2;
            color: #fff;
            border: none;
            border-radius: 20px;
            padding: 8px 16px;
            font-size: 0.9rem;
            cursor: pointer;
            transition: all 0.3s;
        }
        .refresh-btn:hover {
            background: #357ab8;
            transform: translateY(-1px);
        }
        .refresh-btn:active {
            transform: translateY(0);
        }
        .last-update {
            font-size: 0.8rem;
            color: #888;
        }
        
        /* Metrics Grid */
        .metrics-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 32px;
        }
        .metric-card {
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
            border-radius: 12px;
            padding: 20px;
            display: flex;
            align-items: center;
            gap: 16px;
            border: 1px solid #2d2d44;
            transition: all 0.3s;
            position: relative;
            overflow: hidden;
        }
        .metric-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 32px rgba(74, 144, 226, 0.1);
            border-color: #4a90e2;
        }
        .metric-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: linear-gradient(90deg, #4a90e2, #2ecc71);
        }
        .metric-icon {
            font-size: 2rem;
            color: #4a90e2;
            min-width: 48px;
        }
        .metric-content {
            flex: 1;
        }
        .metric-value {
            font-size: 1.8rem;
            font-weight: bold;
            color: #fff;
            margin-bottom: 4px;
        }
        .metric-label {
            font-size: 0.9rem;
            color: #aaa;
            font-weight: 500;
        }
        
        /* Dashboard Grid */
        .dashboard-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 24px;
            margin-bottom: 32px;
        }
        .chart-panel, .activity-panel {
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
            border-radius: 12px;
            padding: 24px;
            border: 1px solid #2d2d44;
        }
        .chart-panel h3, .activity-panel h3 {
            margin: 0 0 20px 0;
            color: #4a90e2;
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 1.1rem;
        }
        
        /* Chart Container */
        #departmentChart {
            max-height: 300px;
            width: 100%;
        }
        
        /* Activity List */
        .activity-list {
            max-height: 300px;
            overflow-y: auto;
        }
        .activity-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 0;
            border-bottom: 1px solid #2d2d44;
        }
        .activity-item:last-child {
            border-bottom: none;
        }
        .activity-icon {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.8rem;
            color: #fff;
        }
        .activity-icon.create { background: #2ecc71; }
        .activity-icon.edit { background: #f39c12; }
        .activity-icon.delete { background: #e63946; }
        .activity-icon.view { background: #4a90e2; }
        .activity-content {
            flex: 1;
        }
        .activity-action {
            font-weight: 500;
            color: #fff;
            margin-bottom: 2px;
        }
        .activity-meta {
            font-size: 0.8rem;
            color: #888;
        }
        
        /* System Grid */
        .system-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 24px;
        }
        .system-panel, .performance-panel {
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
            border-radius: 12px;
            padding: 24px;
            border: 1px solid #2d2d44;
        }
        .system-panel h3, .performance-panel h3 {
            margin: 0 0 20px 0;
            color: #4a90e2;
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 1.1rem;
        }
        .system-stats, .performance-stats {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }
        .stat-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 8px 12px;
            background: rgba(74, 144, 226, 0.1);
            border-radius: 8px;
            border-left: 3px solid #4a90e2;
        }
        .stat-label {
            color: #aaa;
            font-size: 0.9rem;
        }
        .stat-value {
            color: #fff;
            font-weight: 500;
        }
        .stat-value.good { color: #2ecc71; }
        .stat-value.warning { color: #f39c12; }
        .stat-value.danger { color: #e63946; }
        
        .loading {
            text-align: center;
            color: #888;
            padding: 20px;
            font-style: italic;
        }
        
        /* Configuration Styles */
        .config-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 24px;
            padding-bottom: 16px;
            border-bottom: 2px solid #4a90e2;
        }
        .config-controls {
            display: flex;
            gap: 12px;
        }
        .save-config-btn, .reset-config-btn {
            background: #4a90e2;
            color: #fff;
            border: none;
            border-radius: 20px;
            padding: 8px 16px;
            font-size: 0.9rem;
            cursor: pointer;
            transition: all 0.3s;
        }
        .reset-config-btn {
            background: #e63946;
        }
        .save-config-btn:hover {
            background: #357ab8;
        }
        .reset-config-btn:hover {
            background: #c41e3a;
        }
        .config-sections {
            display: flex;
            flex-direction: column;
            gap: 24px;
        }
        .config-section {
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
            border-radius: 12px;
            padding: 24px;
            border: 1px solid #2d2d44;
        }
        .config-section h3 {
            margin: 0 0 20px 0;
            color: #4a90e2;
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 1.1rem;
            border-bottom: 1px solid #2d2d44;
            padding-bottom: 12px;
        }
        .config-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 16px;
        }
        .config-item {
            display: flex;
            flex-direction: column;
            gap: 6px;
        }
        .config-item label {
            color: #aaa;
            font-size: 0.9rem;
            font-weight: 500;
        }
        .config-item input, .config-item select {
            padding: 8px 12px;
            border-radius: 6px;
            border: 1px solid #4a90e2;
            background: #0a1a2f;
            color: #fff;
            font-size: 0.9rem;
            transition: border-color 0.3s;
        }
        .config-item input:focus, .config-item select:focus {
            outline: none;
            border-color: #2ecc71;
            box-shadow: 0 0 0 2px rgba(46, 204, 113, 0.2);
        }
        .config-item input[type="checkbox"] {
            width: auto;
            margin-right: 8px;
        }
        .checkbox-group {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            margin-top: 4px;
        }
        .checkbox-group label {
            display: flex;
            align-items: center;
            gap: 6px;
            margin: 0;
        }
        .checkbox-group input[type="checkbox"] {
            margin: 0;
        }
        
        /* Reports Styles */
        .reports-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 24px;
            padding-bottom: 16px;
            border-bottom: 2px solid #4a90e2;
        }
        .reports-controls {
            display: flex;
            gap: 12px;
        }
        .generate-report-btn {
            background: #2ecc71;
            color: #fff;
            border: none;
            border-radius: 20px;
            padding: 10px 20px;
            font-size: 1rem;
            cursor: pointer;
            transition: all 0.3s;
        }
        .generate-report-btn:hover {
            background: #27ae60;
            transform: translateY(-1px);
        }
        .reports-config {
            display: flex;
            flex-direction: column;
            gap: 24px;
            margin-bottom: 32px;
        }
        .report-type-section, .report-options-section {
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
            border-radius: 12px;
            padding: 24px;
            border: 1px solid #2d2d44;
        }
        .report-type-section h3, .report-options-section h3 {
            margin: 0 0 20px 0;
            color: #4a90e2;
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 1.1rem;
            border-bottom: 1px solid #2d2d44;
            padding-bottom: 12px;
        }
        .report-types {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 16px;
        }
        .report-type-option {
            cursor: pointer;
        }
        .report-type-option input[type="radio"] {
            display: none;
        }
        .report-type-card {
            background: rgba(74, 144, 226, 0.1);
            border: 2px solid #2d2d44;
            border-radius: 12px;
            padding: 20px;
            text-align: center;
            transition: all 0.3s;
        }
        .report-type-option input[type="radio"]:checked + .report-type-card {
            border-color: #4a90e2;
            background: rgba(74, 144, 226, 0.2);
        }
        .report-type-card:hover {
            border-color: #4a90e2;
            transform: translateY(-2px);
        }
        .report-type-card i {
            font-size: 2rem;
            color: #4a90e2;
            margin-bottom: 12px;
        }
        .report-type-card h4 {
            margin: 0 0 8px 0;
            color: #fff;
            font-size: 1.1rem;
        }
        .report-type-card p {
            margin: 0;
            color: #aaa;
            font-size: 0.9rem;
            line-height: 1.4;
        }
        .report-options-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 16px;
        }
        .option-group {
            display: flex;
            flex-direction: column;
            gap: 6px;
        }
        .option-group label {
            color: #aaa;
            font-size: 0.9rem;
            font-weight: 500;
        }
        .option-group select {
            padding: 8px 12px;
            border-radius: 6px;
            border: 1px solid #4a90e2;
            background: #0a1a2f;
            color: #fff;
            font-size: 0.9rem;
        }
        .option-group input[type="checkbox"] {
            margin-right: 8px;
        }
        .report-results {
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
            border-radius: 12px;
            padding: 24px;
            border: 1px solid #2d2d44;
        }
        .report-actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 16px;
            border-bottom: 1px solid #2d2d44;
        }
        .report-actions h3 {
            margin: 0;
            color: #4a90e2;
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 1.1rem;
        }
        .report-export-btns {
            display: flex;
            gap: 8px;
        }
        .export-btn {
            background: #4a90e2;
            color: #fff;
            border: none;
            border-radius: 16px;
            padding: 6px 12px;
            font-size: 0.85rem;
            cursor: pointer;
            transition: all 0.3s;
        }
        .export-btn:hover {
            background: #357ab8;
            transform: translateY(-1px);
        }
        .report-content {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }
        .report-summary {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 16px;
        }
        .summary-card {
            background: rgba(74, 144, 226, 0.1);
            border-radius: 8px;
            padding: 16px;
            border-left: 4px solid #4a90e2;
        }
        .summary-card h4 {
            margin: 0 0 8px 0;
            color: #4a90e2;
            font-size: 0.9rem;
        }
        .summary-card .value {
            font-size: 1.5rem;
            font-weight: bold;
            color: #fff;
        }
        .report-charts {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
        }
        .chart-container {
            background: rgba(26, 26, 46, 0.5);
            border-radius: 8px;
            padding: 16px;
        }
        .chart-container h4 {
            margin: 0 0 12px 0;
            color: #4a90e2;
            font-size: 1rem;
        }
        .report-details {
            background: rgba(26, 26, 46, 0.5);
            border-radius: 8px;
            padding: 16px;
        }
        .details-table {
            width: 100%;
            border-collapse: collapse;
        }
        .details-table th,
        .details-table td {
            padding: 8px 12px;
            text-align: left;
            border-bottom: 1px solid #2d2d44;
        }
        .details-table th {
            background: rgba(74, 144, 226, 0.1);
            color: #4a90e2;
            font-weight: 600;
        }
        .details-table td {
            color: #fff;
        }
        
        /* Responsive */
        @media (max-width: 900px) {
            .dashboard-grid {
                grid-template-columns: 1fr;
            }
            .system-grid {
                grid-template-columns: 1fr;
            }
            .metrics-grid {
                grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            }
            .config-grid {
                grid-template-columns: 1fr;
            }
            .config-controls {
                flex-direction: column;
                width: 100%;
            }
            .config-header {
                flex-direction: column;
                gap: 12px;
                align-items: stretch;
            }
        }
    </style>
</head>
<body>
    <div class="admin-bar">
        <h1>Panel de Administración</h1>
        <button class="logout-btn" onclick="window.location.href='dashboard.html'">
            <i class="fas fa-sign-out-alt"></i> Salir
        </button>
    </div>
    <div class="tabs">
        <button class="tab-btn active" id="tab-dashboard" onclick="showTab('dashboard')">
            <i class="fas fa-tachometer-alt"></i> Dashboard
        </button>
        <button class="tab-btn" id="tab-puertos" onclick="showTab('puertos')">
            <i class="fas fa-ship"></i> Puertos
        </button>
        <button class="tab-btn" id="tab-aeropuertos" onclick="showTab('aeropuertos')">
            <i class="fas fa-plane"></i> Aeropuertos
        </button>
        <button class="tab-btn" id="tab-ferroviarias" onclick="showTab('ferroviarias')">
            <i class="fas fa-train"></i> Ferroviarias
        </button>
        <button class="tab-btn" id="tab-config" onclick="showTab('config')">
            <i class="fas fa-cog"></i> Configuración
        </button>
        <button class="tab-btn" id="tab-reports" onclick="showTab('reports')">
            <i class="fas fa-chart-pie"></i> Reportes
        </button>
    </div>
    <div class="crud-container">
        <!-- Dashboard de Métricas -->
        <div id="dashboard-content">
            <div class="dashboard-header">
                <h2><i class="fas fa-chart-line"></i> Dashboard de Administración</h2>
                <div class="dashboard-controls">
                    <button class="refresh-btn" onclick="refreshDashboard()">
                        <i class="fas fa-sync"></i> Actualizar
                    </button>
                    <span id="last-update" class="last-update">Actualizado: --:--:--</span>
                </div>
            </div>
            
            <!-- Métricas principales -->
            <div class="metrics-grid">
                <div class="metric-card">
                    <div class="metric-icon"><i class="fas fa-database"></i></div>
                    <div class="metric-content">
                        <div class="metric-value" id="metric-total">--</div>
                        <div class="metric-label">Total Infraestructura</div>
                    </div>
                </div>
                <div class="metric-card">
                    <div class="metric-icon"><i class="fas fa-ship"></i></div>
                    <div class="metric-content">
                        <div class="metric-value" id="metric-puertos">--</div>
                        <div class="metric-label">Puertos</div>
                    </div>
                </div>
                <div class="metric-card">
                    <div class="metric-icon"><i class="fas fa-plane"></i></div>
                    <div class="metric-content">
                        <div class="metric-value" id="metric-aeropuertos">--</div>
                        <div class="metric-label">Aeropuertos</div>
                    </div>
                </div>
                <div class="metric-card">
                    <div class="metric-icon"><i class="fas fa-train"></i></div>
                    <div class="metric-content">
                        <div class="metric-value" id="metric-ferroviarias">--</div>
                        <div class="metric-label">Ferroviarias</div>
                    </div>
                </div>
                <div class="metric-card">
                    <div class="metric-icon"><i class="fas fa-check-circle"></i></div>
                    <div class="metric-content">
                        <div class="metric-value" id="metric-operativos">--</div>
                        <div class="metric-label">Operativos</div>
                    </div>
                </div>
                <div class="metric-card">
                    <div class="metric-icon"><i class="fas fa-map-marked"></i></div>
                    <div class="metric-content">
                        <div class="metric-value" id="metric-departamentos">--</div>
                        <div class="metric-label">Departamentos</div>
                    </div>
                </div>
            </div>
            
            <!-- Gráficos y actividad -->
            <div class="dashboard-grid">
                <div class="chart-panel">
                    <h3><i class="fas fa-chart-bar"></i> Top Departamentos</h3>
                    <canvas id="departmentChart"></canvas>
                </div>
                <div class="activity-panel">
                    <h3><i class="fas fa-clock"></i> Actividad Reciente</h3>
                    <div id="activity-list" class="activity-list">
                        <div class="loading">Cargando actividad...</div>
                    </div>
                </div>
            </div>
            
            <!-- Sistema y rendimiento -->
            <div class="system-grid">
                <div class="system-panel">
                    <h3><i class="fas fa-server"></i> Estado del Sistema</h3>
                    <div id="system-stats" class="system-stats">
                        <div class="loading">Cargando estado del sistema...</div>
                    </div>
                </div>
                <div class="performance-panel">
                    <h3><i class="fas fa-tachometer-alt"></i> Rendimiento</h3>
                    <div id="performance-stats" class="performance-stats">
                        <div class="loading">Midiendo rendimiento...</div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Configuración del Sistema -->
        <div id="config-content" style="display: none;">
            <div class="config-header">
                <h2><i class="fas fa-cog"></i> Configuración del Sistema</h2>
                <div class="config-controls">
                    <button class="save-config-btn" onclick="saveConfiguration()">
                        <i class="fas fa-save"></i> Guardar Cambios
                    </button>
                    <button class="reset-config-btn" onclick="resetConfiguration()">
                        <i class="fas fa-undo"></i> Restablecer
                    </button>
                </div>
            </div>
            
            <div class="message" id="config-message"></div>
            
            <div class="config-sections">
                <!-- Configuración del Mapa -->
                <div class="config-section">
                    <h3><i class="fas fa-map"></i> Configuración del Mapa</h3>
                    <div class="config-grid">
                        <div class="config-item">
                            <label>Zoom por Defecto</label>
                            <input type="number" id="default_zoom" min="1" max="20" value="6">
                        </div>
                        <div class="config-item">
                            <label>Latitud del Centro</label>
                            <input type="number" id="center_lat" step="0.001" min="-90" max="90" value="-9.19">
                        </div>
                        <div class="config-item">
                            <label>Longitud del Centro</label>
                            <input type="number" id="center_lng" step="0.001" min="-180" max="180" value="-75.0152">
                        </div>
                        <div class="config-item">
                            <label>Zoom Máximo</label>
                            <input type="number" id="max_zoom" min="1" max="20" value="18">
                        </div>
                        <div class="config-item">
                            <label>Zoom Mínimo</label>
                            <input type="number" id="min_zoom" min="1" max="20" value="4">
                        </div>
                        <div class="config-item">
                            <label>
                                <input type="checkbox" id="clustering_enabled" checked> Habilitar Clustering
                            </label>
                        </div>
                    </div>
                </div>
                
                <!-- Configuración de Paginación -->
                <div class="config-section">
                    <h3><i class="fas fa-list"></i> Configuración de Paginación</h3>
                    <div class="config-grid">
                        <div class="config-item">
                            <label>Items por Página</label>
                            <input type="number" id="items_per_page" min="10" max="1000" value="100">
                        </div>
                        <div class="config-item">
                            <label>Máximo Items por Página</label>
                            <input type="number" id="max_items_per_page" min="100" max="5000" value="500">
                        </div>
                    </div>
                </div>
                
                <!-- Configuración de Visualización -->
                <div class="config-section">
                    <h3><i class="fas fa-eye"></i> Configuración de Visualización</h3>
                    <div class="config-grid">
                        <div class="config-item">
                            <label>
                                <input type="checkbox" id="show_coordinates" checked> Mostrar Coordenadas
                            </label>
                        </div>
                        <div class="config-item">
                            <label>
                                <input type="checkbox" id="show_status_icons" checked> Mostrar Iconos de Estado
                            </label>
                        </div>
                        <div class="config-item">
                            <label>Intervalo de Actualización (segundos)</label>
                            <input type="number" id="auto_refresh_interval" min="5" max="300" value="30">
                        </div>
                        <div class="config-item">
                            <label>Tema</label>
                            <select id="theme">
                                <option value="dark">Oscuro</option>
                                <option value="light">Claro</option>
                            </select>
                        </div>
                    </div>
                </div>
                
                <!-- Configuración de Datos -->
                <div class="config-section">
                    <h3><i class="fas fa-database"></i> Configuración de Datos</h3>
                    <div class="config-grid">
                        <div class="config-item">
                            <label>
                                <input type="checkbox" id="cache_enabled" checked> Habilitar Caché
                            </label>
                        </div>
                        <div class="config-item">
                            <label>Duración del Caché (segundos)</label>
                            <input type="number" id="cache_duration" min="60" max="3600" value="300">
                        </div>
                        <div class="config-item">
                            <label>Máximo Registros para Exportar</label>
                            <input type="number" id="max_export_records" min="100" max="50000" value="10000">
                        </div>
                        <div class="config-item">
                            <label>Formatos de Exportación</label>
                            <div class="checkbox-group">
                                <label><input type="checkbox" id="export_csv" checked> CSV</label>
                                <label><input type="checkbox" id="export_json" checked> JSON</label>
                                <label><input type="checkbox" id="export_kml" checked> KML</label>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Configuración de Rendimiento -->
                <div class="config-section">
                    <h3><i class="fas fa-tachometer-alt"></i> Configuración de Rendimiento</h3>
                    <div class="config-grid">
                        <div class="config-item">
                            <label>
                                <input type="checkbox" id="lazy_loading" checked> Carga Perezosa
                            </label>
                        </div>
                        <div class="config-item">
                            <label>
                                <input type="checkbox" id="image_optimization" checked> Optimización de Imágenes
                            </label>
                        </div>
                        <div class="config-item">
                            <label>
                                <input type="checkbox" id="compression_enabled" checked> Compresión Habilitada
                            </label>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Sistema de Reportes -->
        <div id="reports-content" style="display: none;">
            <div class="reports-header">
                <h2><i class="fas fa-chart-pie"></i> Sistema de Reportes</h2>
                <div class="reports-controls">
                    <button class="generate-report-btn" onclick="generateSelectedReport()">
                        <i class="fas fa-play"></i> Generar Reporte
                    </button>
                </div>
            </div>
            
            <div class="message" id="reports-message"></div>
            
            <div class="reports-config">
                <div class="report-type-section">
                    <h3><i class="fas fa-list"></i> Tipo de Reporte</h3>
                    <div class="report-types">
                        <label class="report-type-option">
                            <input type="radio" name="report_type" value="summary" checked>
                            <div class="report-type-card">
                                <i class="fas fa-chart-bar"></i>
                                <h4>Reporte Resumen</h4>
                                <p>Vista general de toda la infraestructura con estadísticas principales</p>
                            </div>
                        </label>
                        <label class="report-type-option">
                            <input type="radio" name="report_type" value="department">
                            <div class="report-type-card">
                                <i class="fas fa-map-marked-alt"></i>
                                <h4>Reporte por Departamento</h4>
                                <p>Análisis detallado de infraestructura por departamento específico</p>
                            </div>
                        </label>
                        <label class="report-type-option">
                            <input type="radio" name="report_type" value="operational">
                            <div class="report-type-card">
                                <i class="fas fa-check-circle"></i>
                                <h4>Reporte Operacional</h4>
                                <p>Estado operativo y funcionalidad de toda la infraestructura</p>
                            </div>
                        </label>
                    </div>
                </div>
                
                <div class="report-options-section">
                    <h3><i class="fas fa-cog"></i> Opciones del Reporte</h3>
                    <div class="report-options-grid">
                        <div class="option-group" id="department-selector" style="display: none;">
                            <label>Departamento</label>
                            <select id="department-select">
                                <option value="">Seleccionar departamento...</option>
                            </select>
                        </div>
                        <div class="option-group">
                            <label>Formato de Exportación</label>
                            <select id="export-format">
                                <option value="json">JSON</option>
                                <option value="csv">CSV</option>
                                <option value="xml">XML</option>
                            </select>
                        </div>
                        <div class="option-group">
                            <label>
                                <input type="checkbox" id="include-charts" checked> Incluir Gráficos
                            </label>
                        </div>
                        <div class="option-group">
                            <label>
                                <input type="checkbox" id="detailed-view" checked> Vista Detallada
                            </label>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="report-results" id="report-results" style="display: none;">
                <div class="report-actions">
                    <h3><i class="fas fa-file-alt"></i> Resultados del Reporte</h3>
                    <div class="report-export-btns">
                        <button class="export-btn" onclick="exportCurrentReport('json')">
                            <i class="fas fa-download"></i> Exportar JSON
                        </button>
                        <button class="export-btn" onclick="exportCurrentReport('csv')">
                            <i class="fas fa-file-csv"></i> Exportar CSV
                        </button>
                        <button class="export-btn" onclick="exportCurrentReport('xml')">
                            <i class="fas fa-file-code"></i> Exportar XML
                        </button>
                    </div>
                </div>
                
                <div class="report-content">
                    <div class="report-summary" id="report-summary"></div>
                    <div class="report-charts" id="report-charts"></div>
                    <div class="report-details" id="report-details"></div>
                </div>
            </div>
        </div>
        
        <!-- CRUD Content (oculto por defecto) -->
        <div id="crud-content" style="display: none;">
            <div class="crud-header">
                <h2 id="crud-title">Gestión de Puertos</h2>
                <button class="add-btn" onclick="openModal()"><i class="fas fa-plus"></i> Nuevo</button>
            </div>
            <div class="message" id="crud-message"></div>
            <table id="crud-table">
                <thead>
                    <tr id="crud-table-head">
                        <!-- Encabezados dinámicos -->
                    </tr>
                </thead>
                <tbody id="crud-table-body">
                    <!-- Filas dinámicas -->
                </tbody>
            </table>
        </div>
    </div>
    <!-- Modal para crear/editar -->
    <div class="modal-bg" id="modal-bg">
        <div class="modal">
            <h3 id="modal-title">Nuevo Registro</h3>
            <form id="crud-form" autocomplete="off">
                <!-- Campos dinámicos -->
            </form>
            <div class="modal-actions">
                <button class="save-btn" type="submit" form="crud-form">Guardar</button>
                <button class="cancel-btn" onclick="closeModal()">Cancelar</button>
            </div>
        </div>
    </div>
    <script>
    // --- JS para tabs y modal ---
    let currentTab = 'puertos';
    let currentData = [];
    let editingId = null;
    const campos = {
        puertos: [
            { name: 'NOMBRE_TERMINAL', label: 'Nombre del Puerto', required: true },
            { name: 'LOCALIDAD', label: 'Localidad', required: true },
            { name: 'DEPARTAMENTO', label: 'Departamento', required: true },
            { name: 'LATITUD', label: 'Latitud', type: 'number', required: true },
            { name: 'LONGITUD', label: 'Longitud', type: 'number', required: true },
            { name: 'ESTADO', label: 'Estado', required: false },
            { name: 'TITULARIDAD', label: 'Titularidad', required: false },
            { name: 'codigo', label: 'Código', required: false }
        ],
        aeropuertos: [
            { name: 'NOMBRE', label: 'Nombre del Aeropuerto', required: true },
            { name: 'LOCALIDAD', label: 'Localidad', required: true },
            { name: 'DEPARTAMENTO', label: 'Departamento', required: true },
            { name: 'LATITUD', label: 'Latitud', type: 'number', required: true },
            { name: 'LONGITUD', label: 'Longitud', type: 'number', required: true },
            { name: 'ESTADO', label: 'Estado', required: false },
            { name: 'TITULARIDAD', label: 'Titularidad', required: false },
            { name: 'codigo', label: 'Código', required: false }
        ],
        ferroviarias: [
            { name: 'NOMBRE', label: 'Nombre del Ferrocarril', required: true },
            { name: 'CODIGO_FERROVIARIO', label: 'Código Ferroviario', required: false },
            { name: 'DEPARTAMENTO', label: 'Departamento', required: true },
            { name: 'TRAMO', label: 'Tramo', required: true },
            { name: 'SUBTRAMO', label: 'Subtramo', required: false },
            { name: 'LONGITUD', label: 'Longitud (km)', type: 'number', required: false },
            { name: 'ANCHO', label: 'Ancho de Vía', required: false, options: ['Estándar', 'Angosto', 'Métrico'] },
            { name: 'ELECTRIFICACION', label: 'Electrificación', required: false, options: ['Electrificada', 'No electrificada'] },
            { name: 'ESTADO', label: 'Estado', required: false, options: ['Operativo', 'Inoperativo', 'En construcción', 'Abandonado'] },
            { name: 'TITULARIDAD', label: 'Titularidad', required: false, options: ['Pública', 'Privada', 'Pública (concesionada)', 'Mixta'] },
            { name: 'ADMINISTRADOR', label: 'Administrador', required: false },
            { name: 'LATITUD', label: 'Latitud', type: 'number', required: false },
            { name: 'LONGITUD', label: 'Longitud', type: 'number', required: false },
            { name: 'OBSERVACIONES', label: 'Observaciones', required: false }
        ]
    };

    function showTab(tab) {
        currentTab = tab;
        document.getElementById('tab-dashboard').classList.toggle('active', tab==='dashboard');
        document.getElementById('tab-puertos').classList.toggle('active', tab==='puertos');
        document.getElementById('tab-aeropuertos').classList.toggle('active', tab==='aeropuertos');
        document.getElementById('tab-ferroviarias').classList.toggle('active', tab==='ferroviarias');
        document.getElementById('tab-config').classList.toggle('active', tab==='config');
        document.getElementById('tab-reports').classList.toggle('active', tab==='reports');
        
        // Hide all content sections
        document.getElementById('dashboard-content').style.display = 'none';
        document.getElementById('config-content').style.display = 'none';
        document.getElementById('reports-content').style.display = 'none';
        document.getElementById('crud-content').style.display = 'none';
        
        if (tab === 'dashboard') {
            document.getElementById('dashboard-content').style.display = 'block';
            loadDashboard();
        } else if (tab === 'config') {
            document.getElementById('config-content').style.display = 'block';
            loadConfiguration();
        } else if (tab === 'reports') {
            document.getElementById('reports-content').style.display = 'block';
            loadReportsTab();
        } else {
            document.getElementById('crud-content').style.display = 'block';
            
            let titulo = 'Gestión de Puertos';
            if (tab === 'aeropuertos') titulo = 'Gestión de Aeropuertos';
            else if (tab === 'ferroviarias') titulo = 'Gestión de Ferroviarias';
            
            document.getElementById('crud-title').textContent = titulo;
            loadTableData();
        }
    }

    async function loadTableData() {
        setMessage('','');
        document.getElementById('crud-table-head').innerHTML = '';
        document.getElementById('crud-table-body').innerHTML = '<tr><td colspan="99">Cargando...</td></tr>';
        try {
            let tipo = 'puerto';
            if (currentTab === 'aeropuertos') tipo = 'aeropuerto';
            else if (currentTab === 'ferroviarias') tipo = 'ferroviaria';
            
            const res = await fetch(`api_admin.php?tipo=${tipo}`);
            const data = await res.json();
            if (data.error) throw new Error(data.error);
            currentData = data;
            renderTable();
        } catch (e) {
            setMessage('Error al cargar datos: ' + e.message, 'error');
            document.getElementById('crud-table-body').innerHTML = '';
        }
    }

    function renderTable() {
        const camposTab = campos[currentTab];
        // Encabezados
        let headHtml = camposTab.map(c => `<th>${c.label}</th>`).join('');
        headHtml += '<th>Acciones</th>';
        document.getElementById('crud-table-head').innerHTML = headHtml;
        // Filas
        let bodyHtml = '';
        if (!currentData.length) {
            bodyHtml = `<tr><td colspan="${camposTab.length+1}">No hay registros.</td></tr>`;
        } else {
            for (let i = 0; i < currentData.length; i++) {
                const row = currentData[i];
                bodyHtml += '<tr>';
                for (const c of camposTab) {
                    bodyHtml += `<td>${row[c.name] ?? ''}</td>`;
                }
                bodyHtml += `<td class='crud-actions'>
                    <button title='Editar' onclick='openModal(${JSON.stringify(row).replace(/'/g,"&#39;")})'><i class="fas fa-edit"></i></button>
                    <button title='Eliminar' onclick='deleteRegistroByIndex(${i})'><i class="fas fa-trash"></i></button>
                </td>`;
                bodyHtml += '</tr>';
            }
        }
        document.getElementById('crud-table-body').innerHTML = bodyHtml;
    }

    function openModal(editData) {
        document.getElementById('modal-bg').style.display = 'flex';
        const form = document.getElementById('crud-form');
        form.innerHTML = '';
        editingId = null;
        const camposTab = campos[currentTab];
        for (const c of camposTab) {
            const value = editData ? (editData[c.name] ?? '') : '';
            
            if (c.options) {
                // Campo con opciones predefinidas (select)
                let optionsHtml = '<option value="">Seleccionar...</option>';
                for (const option of c.options) {
                    const selected = value === option ? 'selected' : '';
                    optionsHtml += `<option value="${option}" ${selected}>${option}</option>`;
                }
                form.innerHTML += `
                    <label>${c.label}${c.required ? ' *' : ''}
                        <select name="${c.name}" ${c.required ? 'required' : ''}>
                            ${optionsHtml}
                        </select>
                    </label>
                `;
            } else if (c.name === 'OBSERVACIONES') {
                // Campo de texto largo
                form.innerHTML += `
                    <label>${c.label}${c.required ? ' *' : ''}
                        <textarea name="${c.name}" rows="3" ${c.required ? 'required' : ''}>${value}</textarea>
                    </label>
                `;
            } else {
                // Campo normal
                form.innerHTML += `
                    <label>${c.label}${c.required ? ' *' : ''}
                        <input name="${c.name}" type="${c.type||'text'}" value="${value}" ${c.required ? 'required' : ''} />
                    </label>
                `;
            }
        }
        if (editData && editData._id) {
            editingId = editData._id;
        }
        form.onsubmit = saveRegistro;
    }

    function closeModal() {
        document.getElementById('modal-bg').style.display = 'none';
        document.getElementById('crud-form').reset();
        editingId = null;
    }

    async function saveRegistro(e) {
        e.preventDefault();
        setMessage('','');
        const form = e.target;
        const formData = Object.fromEntries(new FormData(form).entries());
        // Convertir tipos de datos
        for (const c of campos[currentTab]) {
            if (c.type === 'number' && formData[c.name]) {
                formData[c.name] = parseFloat(formData[c.name]);
            }
        }
        let tipo = 'puerto';
        if (currentTab === 'aeropuertos') tipo = 'aeropuerto';
        else if (currentTab === 'ferroviarias') tipo = 'ferroviaria';
        
        let method = editingId ? 'PUT' : 'POST';
        let body = { ...formData, tipo };
        if (editingId) body._id = editingId;
        try {
            const res = await fetch('api_admin.php', {
                method,
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(body)
            });
            const data = await res.json();
            if (!data.success && !data.id) throw new Error(data.error || 'Error al guardar');
            setMessage('Registro guardado correctamente','success');
            closeModal();
            loadTableData();
        } catch (e) {
            setMessage('Error: ' + e.message, 'error');
        }
    }

    async function deleteRegistroByIndex(index) {
        const registro = currentData[index];
        if (!registro) {
            setMessage('Error: Registro no encontrado', 'error');
            return;
        }
        
        const id = registro._id;
        
        if (!confirm('¿Seguro que deseas eliminar este registro?')) return;
        setMessage('','');
        let tipo = 'puerto';
        if (currentTab === 'aeropuertos') tipo = 'aeropuerto';
        else if (currentTab === 'ferroviarias') tipo = 'ferroviaria';
        try {
            const res = await fetch('api_admin.php', {
                method: 'DELETE',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ _id: id, tipo })
            });
            const data = await res.json();
            if (!data.success) throw new Error(data.error || 'No se pudo eliminar');
            setMessage('Registro eliminado','success');
            loadTableData();
        } catch (e) {
            setMessage('Error: ' + e.message, 'error');
        }
    }

    function setMessage(msg, type) {
        const el = document.getElementById('crud-message');
        el.textContent = msg;
        el.className = 'message ' + (type||'');
        el.style.display = msg ? 'block' : 'none';
    }

    // === DASHBOARD FUNCTIONS ===
    let dashboardChart = null;

    async function loadDashboard() {
        await Promise.all([
            loadGeneralStats(),
            loadDepartmentChart(),
            loadActivityFeed(),
            loadSystemStats(),
            loadPerformanceStats()
        ]);
        updateLastRefresh();
    }

    async function loadGeneralStats() {
        try {
            const response = await fetch('api_dashboard.php?action=stats');
            const result = await response.json();
            
            if (result.success) {
                const data = result.data;
                document.getElementById('metric-total').textContent = data.total_infraestructura;
                document.getElementById('metric-puertos').textContent = data.puertos;
                document.getElementById('metric-aeropuertos').textContent = data.aeropuertos;
                document.getElementById('metric-ferroviarias').textContent = data.ferroviarias;
                document.getElementById('metric-operativos').textContent = data.operativos.total;
                document.getElementById('metric-departamentos').textContent = data.departamentos_cubiertos;
            }
        } catch (error) {
            console.error('Error loading general stats:', error);
        }
    }

    async function loadDepartmentChart() {
        try {
            const response = await fetch('api_dashboard.php?action=departments');
            const result = await response.json();
            
            if (result.success) {
                const ctx = document.getElementById('departmentChart').getContext('2d');
                
                if (dashboardChart) {
                    dashboardChart.destroy();
                }
                
                dashboardChart = new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: result.data.departments,
                        datasets: [{
                            label: 'Infraestructura por Departamento',
                            data: result.data.counts,
                            backgroundColor: 'rgba(74, 144, 226, 0.8)',
                            borderColor: 'rgba(74, 144, 226, 1)',
                            borderWidth: 1,
                            borderRadius: 4
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                labels: {
                                    color: '#fff'
                                }
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    color: '#aaa'
                                },
                                grid: {
                                    color: 'rgba(255, 255, 255, 0.1)'
                                }
                            },
                            x: {
                                ticks: {
                                    color: '#aaa',
                                    maxRotation: 45
                                },
                                grid: {
                                    color: 'rgba(255, 255, 255, 0.1)'
                                }
                            }
                        }
                    }
                });
            }
        } catch (error) {
            console.error('Error loading department chart:', error);
        }
    }

    async function loadActivityFeed() {
        try {
            const response = await fetch('api_dashboard.php?action=activity');
            const result = await response.json();
            
            if (result.success) {
                const activityList = document.getElementById('activity-list');
                let html = '';
                
                result.data.forEach(activity => {
                    html += `
                        <div class="activity-item">
                            <div class="activity-icon ${activity.type}">
                                <i class="fas fa-${getActivityIcon(activity.type)}"></i>
                            </div>
                            <div class="activity-content">
                                <div class="activity-action">${activity.user} - ${activity.action}</div>
                                <div class="activity-meta">${activity.timestamp}</div>
                            </div>
                        </div>
                    `;
                });
                
                activityList.innerHTML = html || '<div class="loading">No hay actividad reciente</div>';
            }
        } catch (error) {
            console.error('Error loading activity feed:', error);
            document.getElementById('activity-list').innerHTML = '<div class="loading">Error cargando actividad</div>';
        }
    }

    async function loadSystemStats() {
        try {
            const response = await fetch('api_dashboard.php?action=system');
            const result = await response.json();
            
            if (result.success) {
                const systemStats = document.getElementById('system-stats');
                const data = result.data;
                
                systemStats.innerHTML = `
                    <div class="stat-item">
                        <span class="stat-label">PHP Version</span>
                        <span class="stat-value good">${data.php_version}</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-label">Memory Limit</span>
                        <span class="stat-value">${data.memory_limit}</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-label">Disk Usage</span>
                        <span class="stat-value ${data.disk_usage > 80 ? 'danger' : data.disk_usage > 60 ? 'warning' : 'good'}">${data.disk_usage}%</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-label">Free Space</span>
                        <span class="stat-value">${data.disk_free}</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-label">Server Time</span>
                        <span class="stat-value">${data.server_time}</span>
                    </div>
                `;
            }
        } catch (error) {
            console.error('Error loading system stats:', error);
            document.getElementById('system-stats').innerHTML = '<div class="loading">Error cargando estado del sistema</div>';
        }
    }

    async function loadPerformanceStats() {
        try {
            const response = await fetch('api_dashboard.php?action=performance');
            const result = await response.json();
            
            if (result.success) {
                const performanceStats = document.getElementById('performance-stats');
                const data = result.data;
                
                performanceStats.innerHTML = `
                    <div class="stat-item">
                        <span class="stat-label">Response Time</span>
                        <span class="stat-value ${data.response_time > 1000 ? 'danger' : data.response_time > 500 ? 'warning' : 'good'}">${data.response_time} ms</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-label">Memory Usage</span>
                        <span class="stat-value">${data.memory_usage} KB</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-label">Peak Memory</span>
                        <span class="stat-value">${data.peak_memory} MB</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-label">Database</span>
                        <span class="stat-value good">${data.database_status}</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-label">Server Load</span>
                        <span class="stat-value ${data.server_load > 2 ? 'danger' : data.server_load > 1 ? 'warning' : 'good'}">${data.server_load}</span>
                    </div>
                `;
            }
        } catch (error) {
            console.error('Error loading performance stats:', error);
            document.getElementById('performance-stats').innerHTML = '<div class="loading">Error midiendo rendimiento</div>';
        }
    }

    function getActivityIcon(type) {
        switch (type) {
            case 'create': return 'plus';
            case 'edit': return 'edit';
            case 'delete': return 'trash';
            case 'view': return 'eye';
            default: return 'info';
        }
    }

    function updateLastRefresh() {
        const now = new Date();
        const timeString = now.toLocaleTimeString('es-PE', { 
            hour: '2-digit', 
            minute: '2-digit', 
            second: '2-digit' 
        });
        document.getElementById('last-update').textContent = `Actualizado: ${timeString}`;
    }

    function refreshDashboard() {
        const refreshBtn = document.querySelector('.refresh-btn');
        refreshBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Actualizando...';
        refreshBtn.disabled = true;
        
        loadDashboard().finally(() => {
            refreshBtn.innerHTML = '<i class="fas fa-sync"></i> Actualizar';
            refreshBtn.disabled = false;
        });
    }

    // Auto-refresh dashboard every 30 seconds when visible
    setInterval(() => {
        if (currentTab === 'dashboard' && document.getElementById('dashboard-content').style.display !== 'none') {
            loadDashboard();
        }
    }, 30000);

    // === CONFIGURATION FUNCTIONS ===
    async function loadConfiguration() {
        try {
            const response = await fetch('api_config.php');
            const result = await response.json();
            
            if (result.success) {
                populateConfigForm(result.config);
                setConfigMessage('Configuración cargada correctamente', 'success');
            } else {
                setConfigMessage('Error al cargar configuración: ' + result.error, 'error');
            }
        } catch (error) {
            setConfigMessage('Error al cargar configuración: ' + error.message, 'error');
        }
    }

    function populateConfigForm(config) {
        // Map settings
        if (config.map_settings) {
            const map = config.map_settings;
            document.getElementById('default_zoom').value = map.default_zoom || 6;
            document.getElementById('center_lat').value = map.center_lat || -9.19;
            document.getElementById('center_lng').value = map.center_lng || -75.0152;
            document.getElementById('max_zoom').value = map.max_zoom || 18;
            document.getElementById('min_zoom').value = map.min_zoom || 4;
            document.getElementById('clustering_enabled').checked = map.clustering_enabled !== false;
        }
        
        // Pagination settings
        if (config.pagination) {
            const pag = config.pagination;
            document.getElementById('items_per_page').value = pag.items_per_page || 100;
            document.getElementById('max_items_per_page').value = pag.max_items_per_page || 500;
        }
        
        // Display settings
        if (config.display_settings) {
            const display = config.display_settings;
            document.getElementById('show_coordinates').checked = display.show_coordinates !== false;
            document.getElementById('show_status_icons').checked = display.show_status_icons !== false;
            document.getElementById('auto_refresh_interval').value = display.auto_refresh_interval || 30;
            document.getElementById('theme').value = display.theme || 'dark';
        }
        
        // Data settings
        if (config.data_settings) {
            const data = config.data_settings;
            document.getElementById('cache_enabled').checked = data.cache_enabled !== false;
            document.getElementById('cache_duration').value = data.cache_duration || 300;
            document.getElementById('max_export_records').value = data.max_export_records || 10000;
            
            const formats = data.export_formats || ['CSV', 'JSON', 'KML'];
            document.getElementById('export_csv').checked = formats.includes('CSV');
            document.getElementById('export_json').checked = formats.includes('JSON');
            document.getElementById('export_kml').checked = formats.includes('KML');
        }
        
        // Performance settings
        if (config.performance) {
            const perf = config.performance;
            document.getElementById('lazy_loading').checked = perf.lazy_loading !== false;
            document.getElementById('image_optimization').checked = perf.image_optimization !== false;
            document.getElementById('compression_enabled').checked = perf.compression_enabled !== false;
        }
    }

    function getConfigFromForm() {
        const exportFormats = [];
        if (document.getElementById('export_csv').checked) exportFormats.push('CSV');
        if (document.getElementById('export_json').checked) exportFormats.push('JSON');
        if (document.getElementById('export_kml').checked) exportFormats.push('KML');
        
        return {
            map_settings: {
                default_zoom: parseInt(document.getElementById('default_zoom').value),
                center_lat: parseFloat(document.getElementById('center_lat').value),
                center_lng: parseFloat(document.getElementById('center_lng').value),
                max_zoom: parseInt(document.getElementById('max_zoom').value),
                min_zoom: parseInt(document.getElementById('min_zoom').value),
                clustering_enabled: document.getElementById('clustering_enabled').checked
            },
            pagination: {
                items_per_page: parseInt(document.getElementById('items_per_page').value),
                max_items_per_page: parseInt(document.getElementById('max_items_per_page').value)
            },
            display_settings: {
                show_coordinates: document.getElementById('show_coordinates').checked,
                show_status_icons: document.getElementById('show_status_icons').checked,
                auto_refresh_interval: parseInt(document.getElementById('auto_refresh_interval').value),
                theme: document.getElementById('theme').value
            },
            data_settings: {
                cache_enabled: document.getElementById('cache_enabled').checked,
                cache_duration: parseInt(document.getElementById('cache_duration').value),
                export_formats: exportFormats,
                max_export_records: parseInt(document.getElementById('max_export_records').value)
            },
            performance: {
                lazy_loading: document.getElementById('lazy_loading').checked,
                image_optimization: document.getElementById('image_optimization').checked,
                compression_enabled: document.getElementById('compression_enabled').checked
            }
        };
    }

    async function saveConfiguration() {
        const saveBtn = document.querySelector('.save-config-btn');
        const originalText = saveBtn.innerHTML;
        
        try {
            saveBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Guardando...';
            saveBtn.disabled = true;
            
            const config = getConfigFromForm();
            
            const response = await fetch('api_config.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(config)
            });
            
            const result = await response.json();
            
            if (result.success) {
                setConfigMessage('Configuración guardada correctamente', 'success');
            } else {
                setConfigMessage('Error al guardar: ' + (result.error || 'Error desconocido'), 'error');
                if (result.details) {
                    setConfigMessage(result.details.join(', '), 'error');
                }
            }
        } catch (error) {
            setConfigMessage('Error al guardar configuración: ' + error.message, 'error');
        } finally {
            saveBtn.innerHTML = originalText;
            saveBtn.disabled = false;
        }
    }

    async function resetConfiguration() {
        if (!confirm('¿Estás seguro de que deseas restablecer toda la configuración a los valores por defecto?')) {
            return;
        }
        
        const resetBtn = document.querySelector('.reset-config-btn');
        const originalText = resetBtn.innerHTML;
        
        try {
            resetBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Restableciendo...';
            resetBtn.disabled = true;
            
            const response = await fetch('api_config.php', {
                method: 'DELETE'
            });
            
            const result = await response.json();
            
            if (result.success) {
                populateConfigForm(result.config);
                setConfigMessage('Configuración restablecida a valores por defecto', 'success');
            } else {
                setConfigMessage('Error al restablecer: ' + result.error, 'error');
            }
        } catch (error) {
            setConfigMessage('Error al restablecer configuración: ' + error.message, 'error');
        } finally {
            resetBtn.innerHTML = originalText;
            resetBtn.disabled = false;
        }
    }

    function setConfigMessage(msg, type) {
        const el = document.getElementById('config-message');
        el.textContent = msg;
        el.className = 'message ' + (type || '');
        el.style.display = msg ? 'block' : 'none';
        
        // Auto-hide success messages after 3 seconds
        if (type === 'success') {
            setTimeout(() => {
                if (el.textContent === msg) {
                    el.style.display = 'none';
                }
            }, 3000);
        }
    }

    // === REPORTS FUNCTIONS ===
    let currentReport = null;
    let reportCharts = [];

    async function loadReportsTab() {
        await loadDepartmentsList();
        setupReportTypeListeners();
    }

    async function loadDepartmentsList() {
        try {
            const response = await fetch('api_reports.php?action=departments_list');
            const result = await response.json();
            
            if (result.success) {
                const select = document.getElementById('department-select');
                select.innerHTML = '<option value="">Seleccionar departamento...</option>';
                
                result.departments.forEach(dept => {
                    const option = document.createElement('option');
                    option.value = dept;
                    option.textContent = dept;
                    select.appendChild(option);
                });
            }
        } catch (error) {
            console.error('Error loading departments:', error);
        }
    }

    function setupReportTypeListeners() {
        const radioButtons = document.querySelectorAll('input[name="report_type"]');
        radioButtons.forEach(radio => {
            radio.addEventListener('change', function() {
                const departmentSelector = document.getElementById('department-selector');
                if (this.value === 'department') {
                    departmentSelector.style.display = 'flex';
                } else {
                    departmentSelector.style.display = 'none';
                }
            });
        });
    }

    async function generateSelectedReport() {
        const reportType = document.querySelector('input[name="report_type"]:checked').value;
        const generateBtn = document.querySelector('.generate-report-btn');
        const originalText = generateBtn.innerHTML;
        
        try {
            generateBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Generando...';
            generateBtn.disabled = true;
            
            let url = `api_reports.php?action=${reportType}`;
            
            if (reportType === 'department') {
                const department = document.getElementById('department-select').value;
                if (!department) {
                    setReportsMessage('Por favor selecciona un departamento', 'error');
                    return;
                }
                url += `&department=${encodeURIComponent(department)}`;
            }
            
            const response = await fetch(url);
            const result = await response.json();
            
            if (result.success) {
                currentReport = result;
                displayReport(result);
                setReportsMessage('Reporte generado correctamente', 'success');
            } else {
                setReportsMessage('Error al generar reporte: ' + result.error, 'error');
            }
        } catch (error) {
            setReportsMessage('Error al generar reporte: ' + error.message, 'error');
        } finally {
            generateBtn.innerHTML = originalText;
            generateBtn.disabled = false;
        }
    }

    function displayReport(report) {
        document.getElementById('report-results').style.display = 'block';
        
        // Clear existing charts
        reportCharts.forEach(chart => chart.destroy());
        reportCharts = [];
        
        displayReportSummary(report);
        
        if (document.getElementById('include-charts').checked) {
            displayReportCharts(report);
        }
        
        if (document.getElementById('detailed-view').checked) {
            displayReportDetails(report);
        }
    }

    function displayReportSummary(report) {
        const summaryContainer = document.getElementById('report-summary');
        let summaryHTML = '';
        
        if (report.report_type === 'summary') {
            const data = report.data;
            summaryHTML = `
                <div class="summary-card">
                    <h4>Total Infraestructura</h4>
                    <div class="value">${data.totals.total_infraestructura}</div>
                </div>
                <div class="summary-card">
                    <h4>Puertos</h4>
                    <div class="value">${data.totals.puertos}</div>
                </div>
                <div class="summary-card">
                    <h4>Aeropuertos</h4>
                    <div class="value">${data.totals.aeropuertos}</div>
                </div>
                <div class="summary-card">
                    <h4>Ferroviarias</h4>
                    <div class="value">${data.totals.ferroviarias}</div>
                </div>
                <div class="summary-card">
                    <h4>Operativos</h4>
                    <div class="value">${data.operational_status.total_operativos}</div>
                </div>
                <div class="summary-card">
                    <h4>% Operativo</h4>
                    <div class="value">${data.operational_status.porcentaje_operativo}%</div>
                </div>
            `;
        } else if (report.report_type === 'operational') {
            const data = report.data;
            summaryHTML = `
                <div class="summary-card">
                    <h4>Puertos Operativos</h4>
                    <div class="value">${data.puertos.total_operative} (${data.puertos.operative_percentage}%)</div>
                </div>
                <div class="summary-card">
                    <h4>Aeropuertos Operativos</h4>
                    <div class="value">${data.aeropuertos.total_operative} (${data.aeropuertos.operative_percentage}%)</div>
                </div>
                <div class="summary-card">
                    <h4>Ferroviarias Operativas</h4>
                    <div class="value">${data.ferroviarias.total_operative} (${data.ferroviarias.operative_percentage}%)</div>
                </div>
            `;
        } else if (report.department) {
            const data = report.data;
            summaryHTML = `
                <div class="summary-card">
                    <h4>Departamento</h4>
                    <div class="value">${report.department}</div>
                </div>
                <div class="summary-card">
                    <h4>Puertos</h4>
                    <div class="value">${data.puertos.count}</div>
                </div>
                <div class="summary-card">
                    <h4>Aeropuertos</h4>
                    <div class="value">${data.aeropuertos.count}</div>
                </div>
                <div class="summary-card">
                    <h4>Ferroviarias</h4>
                    <div class="value">${data.ferroviarias.count}</div>
                </div>
            `;
        }
        
        summaryContainer.innerHTML = summaryHTML;
    }

    function displayReportCharts(report) {
        const chartsContainer = document.getElementById('report-charts');
        chartsContainer.innerHTML = '';
        
        if (report.report_type === 'summary') {
            createDepartmentDistributionChart(report.data.department_distribution, chartsContainer);
            createInfrastructureTypeChart(report.data.totals, chartsContainer);
        } else if (report.report_type === 'operational') {
            createOperationalStatusChart(report.data, chartsContainer);
        }
    }

    function createDepartmentDistributionChart(data, container) {
        const chartDiv = document.createElement('div');
        chartDiv.className = 'chart-container';
        chartDiv.innerHTML = '<h4>Distribución por Departamento</h4><canvas id="deptDistChart"></canvas>';
        container.appendChild(chartDiv);
        
        const ctx = document.getElementById('deptDistChart').getContext('2d');
        const chart = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: Object.keys(data).slice(0, 10),
                datasets: [{
                    data: Object.values(data).slice(0, 10),
                    backgroundColor: [
                        '#4a90e2', '#2ecc71', '#f39c12', '#e63946', '#9b59b6',
                        '#1abc9c', '#34495e', '#95a5a6', '#d35400', '#c0392b'
                    ]
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        labels: { color: '#fff' }
                    }
                }
            }
        });
        
        reportCharts.push(chart);
    }

    function createInfrastructureTypeChart(data, container) {
        const chartDiv = document.createElement('div');
        chartDiv.className = 'chart-container';
        chartDiv.innerHTML = '<h4>Tipos de Infraestructura</h4><canvas id="infraTypeChart"></canvas>';
        container.appendChild(chartDiv);
        
        const ctx = document.getElementById('infraTypeChart').getContext('2d');
        const chart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: ['Puertos', 'Aeropuertos', 'Ferroviarias'],
                datasets: [{
                    label: 'Cantidad',
                    data: [data.puertos, data.aeropuertos, data.ferroviarias],
                    backgroundColor: ['#4a90e2', '#2ecc71', '#f39c12']
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        labels: { color: '#fff' }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { color: '#aaa' },
                        grid: { color: 'rgba(255, 255, 255, 0.1)' }
                    },
                    x: {
                        ticks: { color: '#aaa' },
                        grid: { color: 'rgba(255, 255, 255, 0.1)' }
                    }
                }
            }
        });
        
        reportCharts.push(chart);
    }

    function createOperationalStatusChart(data, container) {
        const chartDiv = document.createElement('div');
        chartDiv.className = 'chart-container';
        chartDiv.innerHTML = '<h4>Estado Operacional</h4><canvas id="operationalChart"></canvas>';
        container.appendChild(chartDiv);
        
        const ctx = document.getElementById('operationalChart').getContext('2d');
        const chart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: ['Puertos', 'Aeropuertos', 'Ferroviarias'],
                datasets: [{
                    label: 'Operativos',
                    data: [data.puertos.total_operative, data.aeropuertos.total_operative, data.ferroviarias.total_operative],
                    backgroundColor: '#2ecc71'
                }, {
                    label: 'Total',
                    data: [data.puertos.total_items, data.aeropuertos.total_items, data.ferroviarias.total_items],
                    backgroundColor: '#4a90e2'
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        labels: { color: '#fff' }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { color: '#aaa' },
                        grid: { color: 'rgba(255, 255, 255, 0.1)' }
                    },
                    x: {
                        ticks: { color: '#aaa' },
                        grid: { color: 'rgba(255, 255, 255, 0.1)' }
                    }
                }
            }
        });
        
        reportCharts.push(chart);
    }

    function displayReportDetails(report) {
        const detailsContainer = document.getElementById('report-details');
        let detailsHTML = '<h4>Detalles del Reporte</h4>';
        
        if (report.department) {
            detailsHTML += generateDepartmentDetailsTable(report.data);
        } else {
            detailsHTML += `
                <p><strong>Tipo:</strong> ${report.report_type}</p>
                <p><strong>Generado:</strong> ${report.generated_at}</p>
                <p><strong>Tiempo de generación:</strong> ${report.generation_time}</p>
            `;
        }
        
        detailsContainer.innerHTML = detailsHTML;
    }

    function generateDepartmentDetailsTable(data) {
        let html = '<table class="details-table"><thead><tr><th>Tipo</th><th>Nombre</th><th>Localidad</th><th>Estado</th><th>Titularidad</th></tr></thead><tbody>';
        
        ['puertos', 'aeropuertos', 'ferroviarias'].forEach(type => {
            if (data[type] && data[type].items) {
                data[type].items.forEach(item => {
                    html += `
                        <tr>
                            <td>${type.charAt(0).toUpperCase() + type.slice(1)}</td>
                            <td>${item.nombre}</td>
                            <td>${item.localidad}</td>
                            <td>${item.estado}</td>
                            <td>${item.titularidad}</td>
                        </tr>
                    `;
                });
            }
        });
        
        html += '</tbody></table>';
        return html;
    }

    async function exportCurrentReport(format) {
        if (!currentReport) {
            setReportsMessage('No hay reporte para exportar', 'error');
            return;
        }
        
        try {
            let url = `api_reports.php?action=export&type=${currentReport.report_type}&format=${format}`;
            
            if (currentReport.department) {
                url += `&department=${encodeURIComponent(currentReport.department)}`;
            }
            
            const response = await fetch(url);
            const result = await response.json();
            
            if (result.success && result.download_ready) {
                downloadReport(result);
                setReportsMessage(`Reporte exportado en formato ${format.toUpperCase()}`, 'success');
            } else {
                setReportsMessage('Error al exportar reporte: ' + result.error, 'error');
            }
        } catch (error) {
            setReportsMessage('Error al exportar: ' + error.message, 'error');
        }
    }

    function downloadReport(exportData) {
        let content, mimeType;
        
        switch (exportData.format) {
            case 'csv':
                content = convertArrayToCSV(exportData.data);
                mimeType = 'text/csv';
                break;
            case 'json':
                content = JSON.stringify(exportData.data, null, 2);
                mimeType = 'application/json';
                break;
            case 'xml':
                content = exportData.data;
                mimeType = 'application/xml';
                break;
        }
        
        const blob = new Blob([content], { type: mimeType });
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = exportData.filename;
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        window.URL.revokeObjectURL(url);
    }

    function convertArrayToCSV(data) {
        return data.map(row => 
            row.map(cell => 
                typeof cell === 'string' && cell.includes(',') ? `"${cell}"` : cell
            ).join(',')
        ).join('\n');
    }

    function setReportsMessage(msg, type) {
        const el = document.getElementById('reports-message');
        el.textContent = msg;
        el.className = 'message ' + (type || '');
        el.style.display = msg ? 'block' : 'none';
        
        if (type === 'success') {
            setTimeout(() => {
                if (el.textContent === msg) {
                    el.style.display = 'none';
                }
            }, 3000);
        }
    }

    // Al cargar la página, mostrar el dashboard
    showTab('dashboard');
    </script>
</body>
</html>