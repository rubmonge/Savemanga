<!doctype html>
<html>
    <head>
        <meta charset="utf-8">
        <title>Savemanga - manga saver</title>
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.2.0/css/bootstrap.min.css">
        <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.2.0/js/bootstrap.min.js"></script>
    </head>
    <body class="container">
        <header class="row">
            <hgroup>
                <h1>Savemanga</h1>
            </hgroup>
        </header>
        <section>
            <div class="row">
                <div class="col-md-6">
                    <p>
                        Webs suported: mangareader.net, mangapanda.com, narutouchiha.com/manga/, batoto, jesulink, mangafox
                        <br/>
                        <small>soon: submanga.com, manga.animea.net & manga4.com</small>
                    </p>

                    <form method="post" action="process.php" target="process" role="form">
                        <div class="form-group">
                            <p>Example: <strong>http://www.mangareader.net/fairy-tail/300</strong></p>
                            <label for="url">Manga Url/s (for more than one use a pipeline):</label>
                            <textarea class="form-control" name="url" /></textarea>
                            <input type="submit" value="search & save" class="btn btn-default"/>
                        </div>
                    </form>           
                </div>
                <div class="col-md-6">
                    <iframe name="process" src="" style="border:1px solid black; width:100%;height:400px"></iframe>
                </div>
        </section>
    </body>
</html>
