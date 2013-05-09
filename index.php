<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<title>IVR Tester</title>
		<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1, user-scalable=no">
		<meta name="description" content="">
		<meta name="author" content="">

		<!-- Le styles -->
		<link href="https://netdna.bootstrapcdn.com/twitter-bootstrap/2.3.1/css/bootstrap-combined.min.css" rel="stylesheet">
		<link rel="stylesheet" type="text/css" href="styles.css" />
		
		<!-- Le HTML5 shim, for IE6-8 support of HTML5 elements -->
		<!--[if lt IE 9]>
		<script src="https://html5shim.googlecode.com/svn/trunk/html5.js"></script>
		<![endif]-->

		<!-- jQuery -->
		<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.0.0/jquery.min.js"></script>
		<script src="https://netdna.bootstrapcdn.com/twitter-bootstrap/2.3.1/js/bootstrap.min.js"></script>
	</head>
	<body>
		<div class="navbar navbar-static-top navbar-inverse">
			<div class="navbar-inner">
				<a class="brand" href="index.php">IVR Tester</a>
				<div class="container">
					<form class="navbar-form pull-right" method="post" action="index.php">
						<input type="text" class="input-xlarge" placeholder="Enter a URL" name="url" value="<?= $_SESSION['url'] ?>"/>
						<button type="submit" class="btn btn-info" name="start_over" value="1">Enter new URL</button>
					</form>
				</div>
			</div>
		</div>
		<div class="well well-large">
		<?
		if ($_SERVER['REQUEST_METHOD'] == 'POST' && $_POST['url'])
		{
			if ($_POST['start_over'] == 1)
			{
				$_SESSION['url'] = $_POST['url'];
			}
			
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_URL, $_POST['url']);
			
			// if we have user input, then post it
			if ($_POST['user_input'])
			{
				$post_string = "Digits={$_POST['user_input']}";
				curl_setopt($ch, CURLOPT_POST, true);
				curl_setopt($ch, CURLOPT_POSTFIELDS, $post_string);
			}
			
			curl_setopt($ch, CURLOPT_HEADER, false);
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			$result = curl_exec($ch);
			curl_close($ch);
			if(curl_error($ch))
			{
				echo "Curl error: " . curl_error($ch);
				
				break;
			}
			
			if (!preg_match('/\<Say[\s\d\w\=\"\-]*\>(.+)\<\/Say\>/i', $result, $say_matches))
			{
				echo "Parsing error.  No &lt;Say&gt; block.";
			}
			else
			{
				$say = $say_matches[1];
				echo "<p><b>{$say}</b></p>";
			}
			
			$raw = htmlspecialchars($result);
			echo "<p style='text-align:center'><a id='show_raw' href='javascript:show_raw();' class='btn btn-info'>Show Raw Response</a><a id='hide_raw' href='javascript:hide_raw();' class='btn btn-warning'>Hide Raw Response</a><br/><br/><span id='raw'>$raw</span></p>";
			
			// has input
			if (preg_match('/\<Gather(.+)/i', $result))
			{
				if (!preg_match('/action\=\"(.+?)\"\>/i', $result, $action_matches))
				{
					echo "Parsing error.  No action set.";
					break;
				}
				
				$url = $action_matches[1];
				
				preg_match('/numDigits\=\"([0-9]+)\"/', $result, $num_matches);
				
				$num_digs = $num_matches[1];
			?>
				<form method="post" action="index.php">
					<input type="hidden" name="url" value="<?= $url ?>"/>
					<p>
						<span>
							<input type="text" class="input-large" name="user_input" placeholder="Enter response here..." maxlength="<?= $num_digs ?>"/>
							<button type="submit" class="btn btn-success">Submit</button> 
						</span>
						<br/>
						<br/>
						<span class="help-block"><b>Endpoint: </b><?= $url ?></span>
					</p>
				</form>
			<?
			}
			else
			{
				echo '<p class="help-block">*This query had no &lt;Gather&gt; block.</p>';
			}
			
			// finally, check if there's a redirect.  then set a js timer for 5 seconds and then GO! to the redirect
			if(preg_match('/\<Redirect\>(.+?)\<\/Redirect\>/i', $result, $redirect_matches))
			{
				$redirect_to = $redirect_matches[1];			
			?>
				<p>There is a redirect within this request.  I will navigate there in about <b><span id="time_left"></span></b> seconds.<br/><b>New location: </b><?= $redirect_to ?></p>
				<form id="redirect_form" action="index.php" method="post">
					<input type="hidden" name="url" value="<?= $redirect_to ?>"/>
				</form>
				<script>
					var seconds = 5;
					show_time();
					var myVar=setInterval(function(){go_to_redirect()},(seconds * 1000));
					var myVar=setInterval(function(){show_time()},1000);
					
					function go_to_redirect()
					{
						document.getElementById("redirect_form").submit();
					}
					
					function show_time()
					{
						$('#time_left').html(seconds);
						seconds--;
					}
				</script>
			<?
			}
			
		}
		else
		{
		?>
			<h3>Enter a URL to get started testing</h3>
			<form method="post" action="index.php">
				<input type="text" class="input-xxlarge" placeholder="Fully-qualified URL goes here" name="url"/>
				<button type="submit" class="btn btn-info btn-large" name="start_over" value="1">Test!</button>
			</form>
		<?
		}
		?>
		</div>
	</body>
	
	<script>
		$(document).ready(function() {
			hide_raw();
		});
		
		function show_raw()
		{
			$('#show_raw').hide();
			$('#hide_raw').show();
			$('#raw').show();
		}
		
		function hide_raw()
		{
			$('#raw').hide();
			$('#hide_raw').hide();
			$('#show_raw').show();
		}
	</script>
</html>