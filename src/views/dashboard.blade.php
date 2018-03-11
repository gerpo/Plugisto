<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css"
          integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
    <title>Plugisto Packages</title>
</head>
<body class="container">
<div class="d-flex flex-wrap">
    @foreach($packages as $package)
        <a href="{{ $package->route }}">
            <div class="card m-3">
                <img class="card-img-top" src="http://via.placeholder.com/150x150" alt="{{ $package->description }}">
                <div class="card-body">
                    <h5 class="card-title">{{ title_case($package->name) }}</h5>
                </div>
            </div>
        </a>
    @endforeach
</div>
</body>
</html>

