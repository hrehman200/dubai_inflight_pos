<!DOCTYPE html>
<html>
<head>
  <!-- js -->     
<link href="src/facebox.css" media="screen" rel="stylesheet" type="text/css" />
<script src="lib/jquery.js" type="text/javascript"></script>
<script src="src/facebox.js" type="text/javascript"></script>


  <meta charset="UTF-8">
  <link rel="shortcut icon" type="image/x-icon" href="https://production-assets.codepen.io/assets/favicon/favicon-8ea04875e70c4b0bb41da869e81236e54394d63638a1ef12fa558a4a835f1164.ico" />
  <link rel="mask-icon" type="" href="https://production-assets.codepen.io/assets/favicon/logo-pin-f2d2b6d2c61838f7e76325261b7195c27224080bc099486ddd6dccb469b8e8e6.svg" color="#111" />
  <title>CodePen - CSS seat booking</title>
  <script src="http://s.codepen.io/assets/libs/modernizr.js" type="text/javascript"></script>

<meta name="viewport" content="width=device-width">
  
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/normalize/5.0.0/normalize.min.css">


<script type="text/javascript">
  jQuery(document).ready(function($) {
    $('a[rel*=facebox]').facebox({
      loadingImage : 'src/loading.gif',
      closeImage   : 'src/closelabel.png'
    })
  })
</script>
<title>
POS
</title>
<?php
  require_once('auth.php');

?>
       
    <link href="vendors/uniform.default.css" rel="stylesheet" media="screen">
  <link href="css/bootstrap.css" rel="stylesheet">

    <link rel="stylesheet" type="text/css" href="css/DT_bootstrap.css">
  
  <link rel="stylesheet" href="css/font-awesome.min.css">
    <style type="text/css">
      body {
        padding-top: 60px;
        padding-bottom: 40px;
      }
      .sidebar-nav {
        padding: 9px 0;
      }
    </style>
    <link href="css/bootstrap-responsive.css" rel="stylesheet">

  <!-- combosearch box--> 
  
    <script src="vendors/jquery-1.7.2.min.js"></script>
    <script src="vendors/bootstrap.js"></script>

  
  
<link href="../style.css" media="screen" rel="stylesheet" type="text/css" />
<!--sa poip up-->


 <script language="javascript" type="text/javascript">
Begin
var timerID = null;
var timerRunning = false;
function stopclock (){
if(timerRunning)
clearTimeout(timerID);
timerRunning = false;
}
function showtime () {
var now = new Date();
var hours = now.getHours();
var minutes = now.getMinutes();
var seconds = now.getSeconds()
var timeValue = "" + ((hours >12) ? hours -12 :hours)
if (timeValue == "0") timeValue = 12;
timeValue += ((minutes < 10) ? ":0" : ":") + minutes
timeValue += ((seconds < 10) ? ":0" : ":") + seconds
timeValue += (hours >= 12) ? " P.M." : " A.M."
document.clock.face.value = timeValue;
timerID = setTimeout("showtime()",1000);
timerRunning = true;
}
function startclock() {
stopclock();
showtime();
}
window.onload=startclock;
// End -->
</SCRIPT> 

      <style>
      *, *:before, *:after {
  box-sizing: border-box;
}

html {
  font-size: 16px;
}

.plane {
  margin: 100px auto;
  max-width: 1300px;
}

.cockpit {
  height: 200px;
  position: relative;
  overflow: hidden;
  text-align: center;
  border-bottom: 5px solid #d8d8d8;
}
.cockpit:before {
  content: "";
  display: block;
  position: absolute;
  top: 0;
  left: 0;
  height: 500px;
  width: 100%;
  border-radius: 50%;
  border-right: 5px solid #d8d8d8;
  border-left: 5px solid #d8d8d8;
}
.cockpit h1 {
  width: 60%;
  margin: 100px auto 35px auto;
}

.exit {
  position: relative;
  height: 50px;
}
.exit:before, .exit:after {
  content: "EXIT";
  font-size: 14px;
  line-height: 18px;
  padding: 0px 2px;
  font-family: "Arial Narrow", Arial, sans-serif;
  display: block;
  position: absolute;
  background: green;
  color: white;
  top: 50%;
  transform: translate(0, -50%);
}
.exit:before {
  left: 0;
}
.exit:after {
  right: 0;
}

.fuselage {
  border-right: 5px solid #d8d8d8;
  border-left: 5px solid #d8d8d8;
}

ol {
  list-style: none;
  padding: 0;
  margin: 0;
}

.seats {
  display: flex;
  flex-direction: row;
  flex-wrap: nowrap;
  justify-content: flex-start;
}

.seat {
  display: flex;
  flex: 0 0 25.000071428571429%;
  padding: 5px;
  position: relative;
}
.seat:nth-child(4) {
  margin-right: 12.28571428571429%;
}
.seat input[type=checkbox] {
  position: absolute;
  opacity: 0;
}
.seat input[type=checkbox]:checked + label {
  background: #bada55;
  -webkit-animation-name: rubberBand;
  animation-name: rubberBand;
  animation-duration: 300ms;
  animation-fill-mode: both;
}
.seat input[type=checkbox]:disabled + label {
  background: #dddddd;
  text-indent: -9999px;
  overflow: hidden;
}
.seat input[type=checkbox]:disabled + label:after {
  content: "X";
  text-indent: 0;
  position: absolute;
  top: 4px;
  left: 50%;
  transform: translate(-50%, 0%);
}
.seat input[type=checkbox]:disabled + label:hover {
  box-shadow: none;
  cursor: not-allowed;
}
.seat label {
  display: block;
  position: relative;
  width: 100%;
  text-align: center;
  font-size: 14px;
  font-weight: bold;
  line-height: 1.5rem;
  padding: 4px 0;
  background: #F42536;
  border-radius: 5px;
  animation-duration: 300ms;
  animation-fill-mode: both;
}
.seat label:before {
  content: "";
  position: absolute;
  width: 75%;
  height: 75%;
  top: 1px;
  left: 50%;
  transform: translate(-50%, 0%);
  background: rgba(255, 255, 255, 0.4);
  border-radius: 3px;
}
.seat label:hover {
  cursor: pointer;
  box-shadow: 0 0 0px 2px #5C6AFF;
}

@-webkit-keyframes rubberBand {
  0% {
    -webkit-transform: scale3d(1, 1, 1);
    transform: scale3d(1, 1, 1);
  }
  30% {
    -webkit-transform: scale3d(1.25, 0.75, 1);
    transform: scale3d(1.25, 0.75, 1);
  }
  40% {
    -webkit-transform: scale3d(0.75, 1.25, 1);
    transform: scale3d(0.75, 1.25, 1);
  }
  50% {
    -webkit-transform: scale3d(1.15, 0.85, 1);
    transform: scale3d(1.15, 0.85, 1);
  }
  65% {
    -webkit-transform: scale3d(0.95, 1.05, 1);
    transform: scale3d(0.95, 1.05, 1);
  }
  75% {
    -webkit-transform: scale3d(1.05, 0.95, 1);
    transform: scale3d(1.05, 0.95, 1);
  }
  100% {
    -webkit-transform: scale3d(1, 1, 1);
    transform: scale3d(1, 1, 1);
  }
}
@keyframes rubberBand {
  0% {
    -webkit-transform: scale3d(1, 1, 1);
    transform: scale3d(1, 1, 1);
  }
  30% {
    -webkit-transform: scale3d(1.25, 0.75, 1);
    transform: scale3d(1.25, 0.75, 1);
  }
  40% {
    -webkit-transform: scale3d(0.75, 1.25, 1);
    transform: scale3d(0.75, 1.25, 1);
  }
  50% {
    -webkit-transform: scale3d(1.15, 0.85, 1);
    transform: scale3d(1.15, 0.85, 1);
  }
  65% {
    -webkit-transform: scale3d(0.95, 1.05, 1);
    transform: scale3d(0.95, 1.05, 1);
  }
  75% {
    -webkit-transform: scale3d(1.05, 0.95, 1);
    transform: scale3d(1.05, 0.95, 1);
  }
  100% {
    -webkit-transform: scale3d(1, 1, 1);
    transform: scale3d(1, 1, 1);
  }
}
.rubberBand {
  -webkit-animation-name: rubberBand;
  animation-name: rubberBand;
}

    </style>

  <script>
  window.console = window.console || function(t) {};
</script>

  
  
  <script>
  if (document.location.search.match(/type=embed/gi)) {
    window.parent.postMessage("resize", "*");
  }
</script>


</head>
<?php
function createRandomPassword() {
  $chars = "003232303232023232023456789";
  srand((double)microtime()*1000000);
  $i = 0;
  $pass = '' ;
  while ($i <= 7) {

    $num = rand() % 33;

    $tmp = substr($chars, $num, 1);

    $pass = $pass . $tmp;

    $i++;

  }
  return $pass;
}
$finalcode='RS-'.createRandomPassword();
?>
<body>
<?php include('navfixed.php');?>
  <?php
$position=$_SESSION['SESS_LAST_NAME'];
if($position=='cashier') {
?>
<a href="sales.php?id=cash&invoice=<?php echo $finalcode ?>">Cash</a>

<a href="../index.php">Logout</a>
<?php
}
if($position=='admin') {
?>
  
<div class="container-fluid">
      <div class="row-fluid">
  <div class="span2">
          <div class="well sidebar-nav">
              <ul class="nav nav-list"><br>
                <?php
              include "side-menu.php";
            ?>
          <!-- 
              <li><a href="index.php"><i class="icon-dashboard icon-2x"></i> Dashboard </a></li> 
      <li ><a href="sales.php?id=cash&invoice=<?php echo $finalcode ?>"><i class="icon-shopping-cart icon-2x"></i> Sales</a>  </li>             
      <li><a href="products.php"><i class="icon-list-alt icon-2x"></i> Products</a>                                     </li>
      <li><a href="customer.php"><i class="icon-group icon-2x"></i> Customers</a>                                    </li>
      <li><a href="supplier.php"><i class="icon-group icon-2x"></i> Partners</a> 
      <li><a href="accounts.php"><i class="icon-group icon-2x"></i> Business Plan</a>                                    </li>
      <li><a href="accounts.php"><i class="icon-group icon-2x"></i> Accounts</a>                                    </li>
      <li><a href="supplier.php"><i class="icon-group icon-2x"></i> Operators</a>                                    </li>
      <li><a href="salesreport.php?d1=0&d2=0"><i class="icon-bar-chart icon-2x"></i> Sales Report</a>                </li>
      <li class="active"><a href="bookingcalander.php?d1=0&d2=0"><i class="icon-bar-chart icon-2x"></i> Booking Calander</a>                </li>
   -->
      <br><br><br>
      <li>
       <div class="hero-unit-clock">
    
      <form name="clock">
      <font color="white">Time: <br></font>&nbsp;<input style="width:150px;" type="text" class="trans" name="face" value="" disabled>
      </form>
        </div>
      </li>
        
        </ul>    
<?php } ?>        
          </div><!--/.well -->
        </div><!--/span-->
 <script src="js/jquery-1.11.0.min.js"></script>
    <script src="js/jquery-ui.min.js"></script>
    <script type="text/javascript">
        var idleTime = 0;
        
        $(document).ready(function() {
            $("#datePicker").datepicker({
                dateFormat: "yymmdd",
                onSelect: function(dateText, inst) {
                    var data_info = [];
                    data_info.push({name: 'operation', value: 'generate_table'});
                    data_info.push({name: 'date', value: dateText});
                    $.ajax({
                        type: "post",
                        url: "purchase_action.php",
                        data: data_info,
                        success: function(dataActivity) {
                            $("#session-holder2").html(dataActivity);
                        }
                    });
                }
            });
            
            //Increment the idle time counter every minute.
            var idleInterval = setInterval(timerIncrement, 5000); // 5 second
            //
            //Zero the idle timer on mouse movement.
            $(document).mousemove(function (e) {
                idleTime = 0;
            });
            $(document).keypress(function (e) {
                idleTime = 0;
            });
            
        });
        
        // Redirect to home if no user interaction
        function timerIncrement() {
            idleTime ++;
            if (idleTime > 29) { // 1 minute
                window.location.href = 'index.php';
            }
        }
    </script>

<div class="span10">
    <div class="contentheader">
      <i class="icon-money"></i> Booking Calendar
      </div>
      <ul class="breadcrumb">
      <a href="index.php"><li>Dashboard</li></a> /
      <li class="active">Booking Calendar</li>
      </ul>

  <div class="plane">
  <div class="cockpit">
    <h1>Inflight Dubai</h1>
  </div>
  <div class="Show exit--front fuselage">
    
  </div>

  <li class="row row--2">
    

<?php

//$output = "";
        
  for ($i = 0; $i < 24; $i++) {

    echo '<ol class="seats" type="A">';
                
            $hour = $i;

            if ($hour <= 9) {
                $hour = "0".$hour;
            }


            $sessionTime = (string)$hour.":00";

            echo '
            <li class="seat">
                <input type="checkbox" id="2A" />
                <label for="2A">'.$sessionTime.'</label>
            </li>';

            $sessionTime = $hour.":30";
          echo '
            <li class="seat">
                <input type="checkbox" id="2A" />
                <label for="2A">'.$sessionTime.'</label>
            </li>';


            // if (array_search($sessionTime, array_column($this->sessionTimeArray, 'time_of_flight_session')) === FALSE) {
            //     $output .= "<p>".$sessionTime."</p>";
            // } else {
            //     $output .= "<a href=\"share-gallery.php?session=".$sessionTime."\">".$sessionTime."</a>";
            // }
            
      echo '</ol>';

        }
?>

      
    </li>

  <div class="show exit--back fuselage">
    
  </div>
</div>
  
  <script src='//cdnjs.cloudflare.com/ajax/libs/jquery/2.1.3/jquery.min.js'></script>

 <div class="clearfix"></div>
</div>
</div>
</div> 

</body>
<?php include('footer.php');?>
</html>
 