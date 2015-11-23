<?php
	require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'Dispatcher.php');
	$myDispatch = new Dispatcher();
	$skillIds = $myDispatch->invokeCall('getSkills');
 // echo $skillIds;
	// var_dump($skillIds);
	// echo '<a href="controllers/mainController.php/invoke/getArmors/\'\'">GO</a>';

?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <!-- Required meta tags always come first -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="x-ua-compatible" content="ie=edge">

    <!-- Bootstrap CSS -->
		<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.5.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="assets/dist/css/bootstrap.css">
		<link rel="stylesheet" href="assets/dist/css/sticky-footer-nav.css">
		<link rel="stylesheet" href="assets/dist/css/typeahead.css">
  </head>
  <body>
		<!-- Fixed navbar -->
    <nav class="navbar navbar-default navbar-fixed-top">
      <div class="container">
        <div class="navbar-header">
          <a class="navbar-brand" href="#">SASS - SUPER Armor Set Search</a>
        </div>
      </div>
    </nav>

		<!-- Begin page content -->
    <div class="container">
			<div class="row">
			  <div class="col-xs-12 col-sm-6 col-md-8">
					<!-- Split button -->
					<div class="btn-group">
					  <button id="hunter_type_label" type="button" class="btn btn-primary">Hunter Type</button>
					  <button type="button" class="btn btn-primary dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
					    <span class="caret"></span>
					    <span class="sr-only">Toggle Dropdown</span>
					  </button>
					  <ul class="dropdown-menu">
					    <li><a href="#">Blademaster</a></li>
					    <li><a href="#">Gunner</a></li>
					  </ul>
					</div>
					<!-- Split button -->
					<div class="btn-group">
					  <button id="hunter_type_label" type="button" class="btn btn-primary">Armor Rarity</button>
					  <button type="button" class="btn btn-primary dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
					    <span class="caret"></span>
					    <span class="sr-only">Toggle Dropdown</span>
					  </button>
					  <ul class="dropdown-menu">
					    <li><a href="#">10<i class="fa fa-star"></i> & Below</a></li>
					    <li><a href="#">9<i class="fa fa-star" aria-hidden="true"></i> & Below</a></li>
							<li><a href="#">8<i class="fa fa-star" aria-hidden="true"></i> & Below</a></li>
							<li><a href="#">7<i class="fa fa-star" aria-hidden="true"></i> & Below</a></li>
					  </ul>
					</div>
					<div id="skill_selection" style="display: block; padding: 3% 0;">
						<input class="typeahead" type="text" placeholder="Skill 1">
						<input class="typeahead" type="text" placeholder="Skill 2">
						<input class="typeahead" type="text" placeholder="Skill 3">
						<input class="typeahead" type="text" placeholder="Skill 4">
						<input class="typeahead" type="text" placeholder="Skill 5">
					</div>
				</div>
			  <div class="col-xs-6 col-md-4">.col-xs-6 .col-md-4</div>
			</div>
    </div>

    <footer class="footer">
      <div class="container">
        <p class="text-muted">&copy; <a href="https://github.com/stevenng308">Steven</a> <a href="https://www.twitch.tv/thundacleese/profile">Thundaclease</a> <a href="https://github.com/stevenng308">Ng</a></p>
      </div>
    </footer>

    <!-- jQuery first, then Bootstrap JS. -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.4/jquery.min.js"></script>
    <script src="assets/dist/js/bootstrap.min.js"></script>
		<script src="assets/dist/js/typeahead.bundle.min.js"></script>
		<script type="text/javascript">
		  var skillNames = [];
			var skillIdLookup = {};
			var skills = $.parseJSON('<?php echo str_replace("'", "\'", $skillIds); ?>');
			$.each(skills.data, function(key, values){
				skillNames.push(values.name);
				skillIdLookup[values.name] = values.id;
			});
		</script>
		<script src="assets/dist/js/sass.js"></script>
  </body>
</html>
