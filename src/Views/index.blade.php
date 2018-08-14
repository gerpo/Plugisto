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
<div class="card mt-5">
    <h2 class="card-header">Plugisto Packages</h2>
    <div class="card-body">
        <form id="updateForm">
            <table class="table table-hover" id="packageTable">
                <thead>
                <tr>
                    <th scope="col">#</th>
                    <th scope="col">Name</th>
                    <th scope="col">Description</th>
                    <th scope="col">Route</th>
                    <th scope="col">Active</th>
                    <th scope="col"></th>
                </tr>
                </thead>
                <tbody>

                @foreach($packages as $package)
                    <tr>
                        <th scope="row">{{ $package->id }}</th>
                        <td>{{ $package->name }}</td>
                        <td>{{ $package->description }}</td>
                        <td>{{ $package->route }}</td>
                        <td>
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input"
                                       id="{{ $package->id }}" {{$package->is_active ? 'checked' : ''}}>
                                <label class="custom-control-label" for="{{ $package->id }}"></label>
                            </div>
                        </td>
                        <td>
                            @if($package->manually_added)
                                <button class="btn btn-danger" onclick="removePackage({{ $package->id }})">Remove
                                </button>
                            @endif
                        </td>
                    </tr>
                @endforeach

                </tbody>
            </table>
            <button type="submit" class="btn btn-primary float-right">Update</button>
        </form>
    </div>
</div>


<script src="https://unpkg.com/axios/dist/axios.min.js"></script>
<script>


    document.getElementById('updateForm').onsubmit = function (evt) {
        evt.preventDefault();

        var oTable = document.getElementById('packageTable');
        var rowLength = oTable.rows.length;

        var packages = [];
        for (var i = 1; i < rowLength; i++) {

            var oCells = oTable.rows.item(i).cells;

            var packageData = {
                id: oCells.item(0).innerHTML,
                is_active: document.getElementById(oCells.item(0).innerHTML).checked
            };

            packages.push(packageData);
        }

        axios.put('/plugisto', {
            data: packages
        })
            .then(function (response) {
            })
            .catch(function (error) {
                console.log(error);
            });
    };

    function removePackage(id) {
        axios.delete('/plugisto/' + id)
            .then(function (response) {
                document.getElementById(id).closest('tr').remove();
            })
            .catch(function (error) {
                console.log(error);
            });
    }
</script>
</body>
</html>