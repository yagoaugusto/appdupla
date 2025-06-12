<!DOCTYPE html>
<html>
<head>
  <script src="https://cdn.jsdelivr.net/npm/html2canvas@1.4.1/dist/html2canvas.min.js"></script>
</head>
<body>
  <div id="area" style="width:300px; height:200px; background:#ccc; padding:20px">
    <h2>Teste de captura</h2>
  </div>
  <button onclick="capturar()">Capturar</button>

  <script>
    function capturar() {
      html2canvas(document.getElementById('area')).then(canvas => {
        document.body.appendChild(canvas);
      });
    }
  </script>
</body>
</html>