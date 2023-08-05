<?php 
session_start();

if(!isset($_SESSION['user_data']))
{
	header('location:index.php');
}

require('database/ChatUser.php');

require('database/ChatRooms.php');

$chat_object = new ChatRooms;

$chat_data = $chat_object->get_all_chat_data();

$user_object = new ChatUser;

$user_data = $user_object->get_user_all_data();

?>

<!DOCTYPE html>
<html>
<head>	

	<title>WhyChat</title>

	<link rel="stylesheet" href="vendor-front/bootstrap/dark-mode.css">

	<!-- Bootstrap core CSS -->
    <link href="vendor-front/bootstrap/bootstrap.min.css" rel="stylesheet">

    <link href="vendor-front/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">

    <link rel="stylesheet" type="text/css" href="vendor-front/parsley/parsley.css"/>

	<style type="text/css">
		html,
		body {
		  height: 100%;
		  width: 100%;
		  margin: 0;
		}
		#wrapper
		{
			display: flex;
		  	flex-flow: column;
		  	height: 100%;
		}
		#remaining
		{
			flex-grow : 1;
		}
		#messages {
			height: 200px;
			background: whitesmoke;
			overflow: auto;
		}
		#chat-room-frm {
			margin-top: 10px;
		}
		#user_list
		{
			height:450px;
			overflow-y: auto;
		}

		#messages_area
		{
			height: 650px;
			overflow-y: auto;
			background-color:#e6e6e6;
		}

	</style>
</head>
<body>
	
	<div class="custom-control custom-switch">
		<input type="checkbox" class="custom-control-input" id="darkSwitch">
		<label class="custom-control-label" for="darkSwitch">Dark Mode</label>
	</div>

	<div class="container">

		<br />
        <h3 class="text-center">WhyChat</h3>
        <br />
		<div class="row">
			
			<div class="col-lg-8">
				<div class="card">
					<div class="card-header">
						<div class="row">
							<div class="col col-sm-6">
								<h3>Global Chat</h3>
							</div>
							<div class="col col-sm-6 text-right">
								<a href="privatechat.php" class="btn btn-success btn-sm">Private Chat</a>
							</div>
						</div>
					</div>
					<div class="card-body" id="messages_area">
					<?php
					foreach($chat_data as $chat)
					{
						if(isset($_SESSION['user_data'][$chat['userid']]))
						{
							$from = 'Me';
							$row_class = 'row justify-content-end';
							$background_class = 'alert-success';
						}
						else
						{
							$from = $chat['user_name'];
							$row_class = 'row justify-content-start';
							$background_class = 'alert-primary';
						}

						echo '
						<div class="'.$row_class.'">
							<div class="col-sm-10">
								<div class="shadow-sm alert '.$background_class.'">
									<b>'.$from.' - </b>'.$chat["msg"].'
									<br />
									<div class="text-right">
										<small><i>'.$chat["created_on"].'</i></small>
									</div>
								</div>
							</div>
						</div>
						';
					}
					?>
					</div>
				</div>
				
				<form method="post" id="chat_form" data-parsley-errors-container="#validation_error">
					<div class="input-group mb-3">
						<textarea class="form-control" id="chat_message" name="chat_message" placeholder="Type Message Here" 
						data-parsley-maxlength="1000" data-parsley-pattern="/^[a-zA-Z0-9\s]+$/" required></textarea>
						<div class="input-group-append">
							<button type="submit" name="send" id="send" class="btn btn-primary"><i class="fa fa-paper-plane"></i></button>
						</div>
					</div>
					<div id="validation_error"></div>
				</form>
			</div>
			<div class="col-lg-4">
				<?php

				$login_user_id = '';

				foreach($_SESSION['user_data'] as $key => $value)
				{
					$login_user_id = $value['id'];
				?>
				<input type="hidden" name="login_user_id" id="login_user_id" value="<?php echo $login_user_id; ?>" />
				<div class="mt-3 mb-3 text-center">
					<img src="<?php echo $value['profile']; ?>" width="150" class="img-fluid rounded-circle img-thumbnail" />
					<h3 class="mt-2"><?php echo $value['name']; ?></h3>
					<a href="profile.php" class="btn btn-secondary mt-2 mb-2">Edit</a>
					<input type="button" class="btn btn-primary mt-2 mb-2" name="logout" id="logout" value="Logout" />
				</div>
				<?php
				}
				?>

				<div class="card mt-3">
					<div class="card-header">User List</div>
					<div class="card-body" id="user_list">
						<div class="list-group list-group-flush">
						<?php
						if(count($user_data) > 0)
						{
							foreach($user_data as $key => $user)
							{
								$icon = '<i class="fa fa-circle text-danger"></i>';
								$lastseen = $user['user_last_seen'];

								if($user['user_login_status'] == 'Login')
								{
									$icon = '<i class="fa fa-circle text-success"></i>';
									$lastseen = 'Online';
								}

								if($user['user_id'] != $login_user_id)
								{							
									echo '
									<a class="list-group-item list-group-item-action" title="Last Seen: '.$lastseen.'">
										<img src="'.$user["user_profile"].'" class="img-fluid rounded-circle img-thumbnail" width="50" />
										<span class="ml-1"><strong>'.$user["user_name"].'</strong></span>
										<span class="mt-2 float-right">'.$icon.'</span>
									</a>
									';
								}

							}
						}
						?>
						</div>
					</div>
				</div>

			</div>
		</div>
	</div>
	<script src="vendor-front/bootstrap/dark-mode-switch.js"></script>

	<!-- Bootstrap core JavaScript -->
	<script src="vendor-front/jquery/jquery.min.js"></script>
    <script src="vendor-front/bootstrap/js/bootstrap.bundle.min.js"></script>

    <!-- Core plugin JavaScript-->
    <script src="vendor-front/jquery-easing/jquery.easing.min.js"></script>

    <script type="text/javascript" src="vendor-front/parsley/dist/parsley.min.js"></script>
</body>
<script type="text/javascript">
	
	$(document).ready(function(){

		var conn = new WebSocket('ws://localhost:8080');
		conn.onopen = function(e) {
		    console.log("Connection established!");
		};

		conn.onmessage = function(e) {
		    console.log(e.data);

		    var data = JSON.parse(e.data);

			if(data.status_type) // Private chat
			{
				return;
			}

		    var row_class = '';

		    var background_class = '';

		    if(data.from == 'Me')
		    {
		    	row_class = 'row justify-content-end';
		    	background_class = 'alert-success';
		    }
		    else
		    {
				row_class = 'row justify-content-start';
				background_class = 'alert-primary';
		    }

			var html_data = "<div class='"+row_class+"'><div class='col-sm-10'><div class='shadow-sm alert "+background_class+"'><b>"
			+data.from+" - </b>"+data.msg+"<br /><div class='text-right'><small><i>"+data.dt+"</i></small></div></div></div></div>";

			$('#messages_area').append(html_data);
				
			$("#chat_message").val("");
			
		};

		$('#chat_form').parsley();

		$('#messages_area').scrollTop($('#messages_area')[0].scrollHeight);

		$('#chat_form').on('submit', function(event){

			event.preventDefault();

			if($('#chat_form').parsley().isValid())
			{

				var user_id = $('#login_user_id').val();

				var message = $('#chat_message').val();

				var data = {
					userId: user_id,
					msg: message,
					command: 'group'
				};

				conn.send(JSON.stringify(data));

				$('#messages_area').scrollTop($('#messages_area')[0].scrollHeight);

			}

		});
		
		$('#logout').click(function(){

			user_id = $('#login_user_id').val();

			$.ajax({
				url:"action.php",
				method:"POST",
				data:{user_id:user_id, action:'leave'},
				success:function(data)
				{
					var response = JSON.parse(data);

					if(response.status == 1)
					{
						conn.close();
						location = 'index.php';
					}
				}
			})

		});

	});
	
</script>

<style>
.sidenav { /* background */
    height: 100%;
    width: 230px;
    position: fixed;
    z-index: 1;
    top: 0;
    right: 0;
    background-color: #ffffff;
    overflow-x: hidden;
    padding-top: 20px;
}

.sidenav a { /* text */
    padding: 6px 8px 6px 16px;
    text-decoration: none;
    font-size: 15px;
    color: #818181;
    display: block;
}

.sidenav a:hover {
    color: #f1f1f1;
    font-size: 15px;
}
.sidenav form { /* upload font */
    padding: 6px 8px 6px 16px;
    text-decoration: none;
    font-size: 17.5px;
    color: #818181;
    display: block;
}


</style>
<div class="sidenav">
  <form action="upload.php" method="post" enctype="multipart/form-data">
    File Sharing:
    <input type="file" name="fileToUpload" id="fileToUpload">
    <input type="submit" value="Upload" name="submit">
</form>
<?php
$files = scandir('./uploads');
sort($files); // this does the sorting

echo "<a href='uploads/'>'Uploads Directory'</a>";
foreach($files as $file){
	echo'<a href="uploads/'.$file.'">'.$file.'</a>';
 }
?>

</div>
</html>