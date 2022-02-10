<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="ie=edge">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>Autocomplete Search Using Typeahead JS</title>
  <script src="//ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-3-typeahead/4.0.1/bootstrap3-typeahead.min.js"></script>


 <style>
    .container{
    padding: 10%;
    text-align: center;
   } 
 </style>
</head>
<body>
 
<div class="container">
    <div class="row">
        <div class="col-12"><h2>Laravel AutoComplete Search Using Typeahead JS</h2></div>
        <div class="col-12">
            <div id="custom-search-input">
                <div class="input-group">
                    <input id="search" name="search" type="text" class="form-control" placeholder="Search" />
                </div>
            </div>
        </div>
    </div>
</div>
 
<script type="text/javascript">
    var route = "{{ url('autocomplete') }}";
    $('#search').typeahead({
        source:  function (term, process) {
        return $.get(route, { term: term }, function (data) {
                return process(data);
            });
        }
    });
</script>
   
</body>
</html>
