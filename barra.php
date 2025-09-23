<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Barra de Progreso con Meta</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
            padding: 20px;
        }
        
        .container {
            background-color: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
            padding: 30px;
            width: 90%;
            max-width: 600px;
            text-align: center;
        }
        
        h1 {
            color: #2c3e50;
            margin-bottom: 30px;
            font-weight: 600;
        }
        
        .meta-info {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            font-weight: 500;
            color: #34495e;
        }
        
        .progress-container {
            background-color: #ecf0f1;
            height: 30px;
            border-radius: 15px;
            margin: 20px 0;
            overflow: hidden;
            position: relative;
            box-shadow: inset 0 2px 8px rgba(0, 0, 0, 0.1);
        }
        
        .progress-bar {
            height: 100%;
            border-radius: 15px;
            background: linear-gradient(90deg, #3498db 0%, #2ecc71 100%);
            width: 0%;
            transition: width 0.8s ease-in-out;
            position: relative;
        }
        
        .excess-bar {
            height: 100%;
            background: linear-gradient(90deg, #2ecc71 0%, #f1c40f 100%);
            width: 0%;
            transition: width 0.8s ease-in-out;
        }
        
        .progress-marker {
            position: absolute;
            top: -5px;
            height: 40px;
            width: 3px;
            background-color: #e74c3c;
            z-index: 10;
        }
        
        .progress-text {
            font-size: 1.2rem;
            font-weight: 600;
            margin: 25px 0;
            color: #2c3e50;
        }
        
        .controls {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            justify-content: center;
            margin-top: 30px;
        }
        
        input[type="number"] {
            padding: 10px 15px;
            border: 2px solid #ddd;
            border-radius: 8px;
            font-size: 1rem;
            width: 120px;
            transition: border-color 0.3s;
        }
        
        input[type="number"]:focus {
            border-color: #3498db;
            outline: none;
        }
        
        button {
            padding: 10px 20px;
            background-color: #3498db;
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 1rem;
            font-weight: 500;
            transition: background-color 0.3s, transform 0.2s;
        }
        
        button:hover {
            background-color: #2980b9;
            transform: translateY(-2px);
        }
        
        .result {
            margin-top: 20px;
            padding: 15px;
            border-radius: 8px;
            background-color: #f8f9fa;
            font-size: 1.1rem;
        }
        
        .achieved {
            color: #27ae60;
            font-weight: 600;
        }
        
        .excess {
            color: #f39c12;
            font-weight: 600;
        }
        
        .marker-label {
            position: absolute;
            top: -25px;
            left: 0;
            transform: translateX(-50%);
            background-color: #e74c3c;
            color: white;
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 0.8rem;
            white-space: nowrap;
        }
        
        .marker-label:after {
            content: '';
            position: absolute;
            top: 100%;
            left: 50%;
            margin-left: -5px;
            border-width: 5px;
            border-style: solid;
            border-color: #e74c3c transparent transparent transparent;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Progreso hacia la Meta</h1>
        
        <div class="meta-info">
            <span>0%</span>
            <span>Meta: <span id="meta-value">5.00</span>%</span>
            <span>100%</span>
        </div>
        
        <div class="progress-container">
            <div class="progress-bar" id="progress-bar"></div>
            <div class="excess-bar" id="excess-bar"></div>
            <div class="progress-marker" id="progress-marker" style="left: 5%;">
                <div class="marker-label">Meta</div>
            </div>
        </div>
        
        <div class="progress-text" id="progress-text">
            Progreso: <span id="current-progress">0</span>%
        </div>
        
        <div class="result" id="result">
            Introduce tu progreso actual para verificar el cumplimiento de la meta.
        </div>
        
        <div class="controls">
            <input type="number" id="progress-input" min="0" max="200" step="0.1" placeholder="Progreso (%)">
            <button id="update-btn">Actualizar Progreso</button>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const progressBar = document.getElementById('progress-bar');
            const excessBar = document.getElementById('excess-bar');
            const progressText = document.getElementById('progress-text');
            const resultDiv = document.getElementById('result');
            const progressInput = document.getElementById('progress-input');
            const updateBtn = document.getElementById('update-btn');
            const currentProgressSpan = document.getElementById('current-progress');
            const progressMarker = document.getElementById('progress-marker');
            const metaValueSpan = document.getElementById('meta-value');
            
            const meta = 5.00; // Meta fija del 5%
            
            // Actualizar la visualización de la meta
            metaValueSpan.textContent = meta.toFixed(2);
            progressMarker.style.left = `${meta}%`;
            
            function updateProgress() {
                let progress = parseFloat(progressInput.value) || 0;
                
                // Asegurarse de que el progreso esté entre 0 y 200
                progress = Math.min(Math.max(progress, 0), 200);
                progressInput.value = progress.toFixed(2);
                
                // Actualizar el texto de progreso
                currentProgressSpan.textContent = progress.toFixed(2);
                
                // Calcular el progreso relativo a la meta
                const relativeProgress = (progress / meta) * 100;
                
                // Actualizar las barras de progreso
                if (progress <= meta) {
                    progressBar.style.width = `${progress}%`;
                    excessBar.style.width = '100%';
                } else {
                    progressBar.style.width = '100%';
                    // Mostrar el excedente más allá del 100%
                    const excess = ((progress - meta) / meta) * 100;
                    excessBar.style.width = `${excess}%`;
                }
                
                // Actualizar el texto de resultado
                if (progress < meta) {
                    const remaining = (meta - progress).toFixed(2);
                    resultDiv.innerHTML = `Te falta un <span class="achieved">${remaining}%</span> para alcanzar la meta.`;
                } else if (progress === meta) {
                    resultDiv.innerHTML = `<span class="achieved">¡Felicidades! Has alcanzado exactamente la meta.</span>`;
                } else {
                    const excess = (progress - meta).toFixed(2);
                    const overAchievement = ((progress / meta - 1) * 100).toFixed(2);
                    resultDiv.innerHTML = `Has superado la meta por <span class="excess">${excess}%</span> (${overAchievement}% más de lo requerido).`;
                }
            }
            
            updateBtn.addEventListener('click', updateProgress);
            
            progressInput.addEventListener('keyup', function(event) {
                if (event.key === 'Enter') {
                    updateProgress();
                }
            });
            
            // Inicializar con un valor por defecto
            progressInput.value = "5.10";
            updateProgress();
        });
    </script>
</body>
</html>