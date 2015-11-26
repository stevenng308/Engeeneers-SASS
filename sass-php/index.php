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
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css" integrity="sha512-dTfge/zgoMYpP7QbHy4gWMEGsbsdZeCXz7irItjcC3sPUFtf0kuFbDz/ixG7ArTxmDjLXDmezHubeNikyKGVyQ==" crossorigin="anonymous">
		<link rel="stylesheet" href="assets/dist/css/sticky-footer-nav.css">
		<link rel="stylesheet" href="assets/dist/css/typeahead.css">
  </head>
  <body>
		<!-- Fixed navbar -->
    <nav class="navbar navbar-default navbar-static-top">
      <div class="container">
        <div class="navbar-header">
          <h1>MH4U SASS - Monster Hunter 4 Ult. SUPER Armor Set Search</h1>
        </div>
      </div>
    </nav>

		<!-- Begin page content -->
    <div class="container" srtle="min-width: 518px">
			<div class="row">
			  <div class="col-xs-7 col-sm-7 col-md-7">
					<!-- Split button -->
					<div class="btn-group">
					  <button id="hunter_type_label" type="button" class="btn btn-primary filter_label">Hunter Type</button>
					  <button type="button" class="btn btn-primary dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
					    <span class="caret"></span>
					    <span class="sr-only">Toggle Dropdown</span>
					  </button>
					  <ul class="dropdown-menu">
					    <li><a class="hunter_type default" href="#" data-value="blade">Blademaster</a></li>
					    <li><a class="hunter_type" href="#" data-value="gunner">Gunner</a></li>
					  </ul>
					</div>
					<!-- Split button -->
					<div class="btn-group">
					  <button id="armor_rarity_label" type="button" class="btn btn-primary filter_label">Armor Rarity</button>
					  <button type="button" class="btn btn-primary dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
					    <span class="caret"></span>
					    <span class="sr-only">Toggle Dropdown</span>
					  </button>
					  <ul class="dropdown-menu">
					    <li><a class="armor_rarity" href="#" data-value="10">10<i class="fa fa-star" style="font-size: 0.97rem;"></i> Only</a></li>
					    <li><a class="armor_rarity" href="#" data-value="9">9<i class="fa fa-star" style="font-size: 0.97rem;"></i> & Above</a></li>
							<li><a class="armor_rarity default" href="#" data-value="8">8<i class="fa fa-star" style="font-size: 0.97rem;"></i> & Above</a></li>
							<li><a class="armor_rarity" href="#" data-value="7">7<i class="fa fa-star" style="font-size: 0.97rem;"></i> & Above</a></li>
					  </ul>
					</div>
					<!-- Split button -->
					<div class="btn-group">
					  <button id="weapon_slot_label" type="button" class="btn btn-primary filter_label">Weapon Slots</button>
					  <button type="button" class="btn btn-primary dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
					    <span class="caret"></span>
					    <span class="sr-only">Toggle Dropdown</span>
					  </button>
					  <ul class="dropdown-menu">
					    <li><a class="weapon" href="#" data-value="3">OOO</a></li>
					    <li><a class="weapon default" href="#" data-value="2">OO-</a></li>
							<li><a class="weapon" href="#" data-value="1">O--</a></li>
							<li><a class="weapon" href="#" data-value="0">None</a></li>
					  </ul>
					</div>
					<button class="btn btn-danger" style="margin-left: 5%;">Reset</button>
					<div id="skill_selection" style="display: block; padding: 3% 0;">
						<input class="typeahead" type="text" placeholder="Skill 1">
						<input class="typeahead" type="text" placeholder="Skill 2">
						<input class="typeahead" type="text" placeholder="Skill 3">
						<input class="typeahead" type="text" placeholder="Skill 4">
						<input class="typeahead" type="text" placeholder="Skill 5">
					</div>
					<div id="submit_container" style="width: 405px;">
						<button type="button" class="btn btn-primary btn-lg btn-block">Search</button>
					</div>
				</div>
			  <div class="col-xs-5 col-sm-5 col-md-5" style="min-height: 458px; max-height: 730px; overflow-y: auto;">
					<h2 style="margin-top: 0px;">Search Results</h2>
					<div style="min-height: 408px">
						<div id="results_container">
							<!--<h4>0 results found</h4>-->
							<div id="search_results">
								<div class="panel panel-primary">
									<div class="panel-heading">
										<h3 class="panel-title">Result 0</h3>
									</div>
									<div class="panel-body">
										<table class="table table-condensed">
											<thead>
												<th>Type</th>
												<th>Name</th>
											</thead>
											<tbody class="armor_list">
											</tbody>
											<thead>
												<th colspan=2>Decorations</th>
											</thead>
											<tbody class="decor_list">
											</tbody>
										</table>
									</div>
								</div>
							</div>
						</div>
						<div id="progress_bar" class="progress" style="margin-top:2%; display:none;">
						  <div class="progress-bar progress-bar-success progress-bar-striped active" role="progressbar" aria-valuenow="45" aria-valuemin="0" aria-valuemax="100" style="width: 0%;">
						    <span class="sr-only">45% Complete</span>
						  </div>
						</div>
					</div>
				</div>
			</div>
    </div>

    <footer class="footer">
      <div class="container">
        <p class="text-muted">&copy; <a href="https://github.com/stevenng308">Steven</a> <a href="https://www.twitch.tv/thundacleese/profile">Thundaclease</a> <a href="https://github.com/stevenng308">Ng</a></p>
      </div>
    </footer>

		<div id="result_template" style="display: none;">
			<div class="search_results">
				<div class="panel panel-primary">
					<div class="panel-heading">
						<h3 class="panel-title"></h3>
					</div>
					<div class="panel-body">
						<table class="table table-condensed">
							<thead>
								<th>Type</th>
								<th>Name</th>
							</thead>
							<tbody class="armor_list">
							</tbody>
							<thead>
								<th>Decoration</th>
								<th>Amount</th>
							</thead>
							<tbody class="decor_list">
							</tbody>
						</table>
					</div>
				</div>
			</div>
			<div id="extra_slots_template">
				<table>
					<thead>
						<th>Extra Slots</th>
						<th>Amount</th>
					</thead>
					<tbody class="extra_slot_list">
					</tbody>
				</table>
			</div>
		</div>
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
				skillIdLookup[values.name.toLowerCase()] = values.id;
			});
		</script>
		<script src="assets/dist/js/sass.js"></script>
  </body>
</html>
