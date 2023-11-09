<!DOCTYPE html>
<html lang='de'>
<head>
    <title>Ajax und JSON</title>
    <meta http-equiv="Content-type" content="text/html; charset=utf-8" />
    <script type="text/javascript" src="js/jquery-3.4.0.min.js"></script>
    <script type="text/javascript">
        $(document).ready(function() {
            $("#header").bind("click", function(event) { // selector "div h3"
                ajax({'func': 1});
            });
        });
        function ajax(data) {
            $.ajax({
                url: 'api.php',
                type: "POST",
                data: data,
                dataType: "text",
                error: error,
                success: success
            });
        }
        function error() {
            alert('Error by data loading!');
        }
        function success(result) {
            var result = $.parseJSON(result);
            var str = '';
            for (var i in result)
                str += '<b>' + i + '</b>: ' + result[i] + '<br />';
            $('#result').empty();       // leer element
            $('#result').append(str);
        }
    </script>
</head>
<body>
    <div>
        <h3 id="header" style='cursor: pointer;'>Get random User from Database!</h3>
    </div>
    <div id="result"></div>
</body>
</html>