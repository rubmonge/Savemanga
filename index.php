<!doctype html>
<html>
    <head>
        <meta charset="utf-8">
        <title>Savemanga - manga saver</title>
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap-theme.min.css">
        <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
    </head>
    <body class="container">
        <section>
            <div class="page-header">
                <h1>Savemanga</h1>
                </div>

            <div class="row">
                <div class="col-md-12">
                    <p>Webs suported: mangareader.net, mangapanda.com, batoto, jesulink, mangafox, jockerfansub</p>
            
                    <form method="post" action="process.php" target="process" role="form" class="row">
                        <div class="col-md-10">
                            <label for="url">Manga Url/s (for more than one use a pipeline):</label>
                            <textarea class="form-control" name="url" /></textarea>                        
                        </div>
                        <div class="col-md-2">
                            <input type="submit" value="search & save" class="btn btn-default"/>
                        </div>
                    </form>           
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <iframe name="process" src="" style="border:1px solid black; width:100%;height:300px"></iframe>
                </div>
            </div>
        </section>
    </body>
</html>
