<html>
<style>
a {
    margin-left: 100px;
}
 img {
  float: left;
  clear: none;
}
</style>

<body>
<br>
<div>
<img src = "wt logo.jpg" width = "80" height = "80"/>

<form action = "brain.php" method = "post">
<input type = "text" name = "query" size = "50">
<input type = "submit" name ="search" value = "Search!">
</div>
<br>
<br><br>
</form>
</body>
</html>

<?php
function value($count, $const)
{
	if($count == 1)
		{
				return ($const);
		}
	else if($count == 2)
		{
			return ($const * 1.5);
		} 
	else if($count > 2)
		{
			return ($const * 1.5 + log($count)); 
		}
}

function PageRank( $attribute, $count, $const)
{
	if($attribute == 'Title_c')
		{
			return (value($count,$const));
		}
	else if($attribute == 'Author_c')
		{
			return (value($count,$const));
		}
	else if($attribute == 'Abstract_c')
		{	
			return (value($count,$const/4));
		}
	else if($attribute == 'Keyword_c')
		{
			return (value($count,$const/2));
		}
		else if($attribute == 'Citation')
		{
			if($count <= 1000)
			return sqrt($count);
			else
			return (sqrt(1000) + log(($count - 1000)));
		}
}
$mainquery = "";
if(isset($_GET["query"]))
{
	$mainquery = $_GET["query"];
}
else
{
  $mainquery = $_POST["query"];
}
$weight = 20;
error_reporting(E_ERROR | E_PARSE);
$anyresult = 0;
$mysql_host = "localhost";
$mysql_user = "root";
$mysql_password = "";
$mysql_db = "ASEO2";
$mysql_db2 = "ASEO";
$mysql_db3 = "SYNONYM";
$con=mysqli_connect($mysql_host,$mysql_user,$mysql_password,$mysql_db);
$con2=mysqli_connect($mysql_host,$mysql_user,$mysql_password,$mysql_db2);
$con3=mysqli_connect($mysql_host,$mysql_user,$mysql_password,$mysql_db3);
$link = mysql_connect($mysql_host, $mysql_user, $mysql_password);
mysql_select_db($mysql_db, $link);
$command = escapeshellcmd('python /opt/lampp/htdocs/seo/lematize.py '.$mainquery);
$output = shell_exec($command);
echo $output;
$file = fopen("query2.txt","r");
$query = fgets($file);
fclose($file);
$query = trim($query);
$listofwords = explode(" ",$query);
$synonym = array();

mysql_select_db($mysql_db3, $link);
#echo "In here <br>";
foreach($listofwords as $token)
{
	$query = mysql_query("SELECT 1 FROM ".$token, $link);
	$num_rows = mysql_num_rows($query);
	if($num_rows > 0)
	{
	   #echo "Word ".$token." exists<br>";
	   $result = mysqli_query($con3,"SELECT * from ".$token);
		 $ans = "";
		while($row = mysqli_fetch_array($result))
		{
			$ans = $ans + $row['val'] + " ";
		}
		$synonym[$token] = explode(" ",trim($ans));
	}
	else
	{
		#echo "Word ".$token." doesn't exists<br>";
	  $synonym = array();
		$command = escapeshellcmd('python /opt/lampp/htdocs/seo/similarity.py '.$mainquery);
		$output = shell_exec($command);	
		$file = fopen('/opt/lampp/htdocs/seo/synonyms.txt',r);
	
		$index = 0;
		while(!feof($file))
  	{
    $temp = fgets($file);
    $temp2 = explode(" ",$temp);
    $synonym[$listofwords[$index]] = array_slice($temp2,1);
    $index = $index + 1;
  	}
    foreach($synonym as $key=>$syns)
    {
    		$query = mysql_query("SELECT 1 FROM ".$key, $link);
				$num_rows = mysql_num_rows($query);
				if($num_rows > 0)
				 {continue;}
				 $query = "CREATE TABLE ".$key." (val varchar(80))";
				 mysqli_query($con3,$query);
				# print_r($syns);
      foreach($syns as $value)
      {
				 $query = "INSERT INTO ".$key." VALUES('".$value."')";
				 mysqli_query($con3,$query);
      }
    }
		echo $output;
		
	  break;
	}
}

$pageranks = array();
$titles = array();
$file = fopen("query2.txt","r");
$query = fgets($file);
fclose($file);
$query = trim($query);
#echo "<br><br> $query<br><br>";
$listofwords = explode(" ",$query);
#echo "<br><br> List of words <br><br";
#print_r($listofwords);
mysql_select_db($mysql_db, $link);
$authors = array();
$authors2 = array();
$citations = array();
foreach ($listofwords as $token)
{
  $token = preg_replace("/[^A-Za-z0-9]/",'',$token);
  $token = strtolower($token);
  $query = mysql_query("SELECT 1 FROM ".$token, $link);
	$num_rows = mysql_num_rows($query);
	if($num_rows > 0)
	{
		$anyresult = 1;
		$result = mysqli_query($con,"SELECT * from ".$token);
		while($row = mysqli_fetch_array($result))
		{
		$result2 = mysqli_query($con2,"SELECT * from SECOND_TABLE WHERE PID = ".$row['PID']);
		$row2 = mysqli_fetch_array($result2);
		if(!array_key_exists($row['PID'],$pageranks))
		{
			$titles[$row2['PID']] = $row2['PAPER_NAME'];
			$pageranks[$row['PID']] = 0;
		}
		  $citations[$row['PID']] = $row2['CITATION_COUNT'];
		  $authors2[$row['PID']] = $row2['AUTHOR'];
			$pageranks[$row['PID']] = $pageranks[$row['PID']] + PageRank('Title_c',$row['Title_c'],$weight);
			$pageranks[$row['PID']] = $pageranks[$row['PID']] + PageRank('Author_c',$row['Author_c'],$weight);
			$pageranks[$row['PID']] = $pageranks[$row['PID']] + PageRank('Abstract_c',$row['Abstract_c'],$weight);
			$pageranks[$row['PID']] = $pageranks[$row['PID']] + PageRank('Keyword_c',$row['Keyword_c'],$weight);
			$pageranks[$row['PID']] = $pageranks[$row['PID']] + PageRank('Citation',$row2['CITATION_COUNT'],$weight);
		}
	}
	foreach($synonym[$token] as $syns)
	{
		$syns = preg_replace("/[^A-Za-z0-9]/",'',$syns);
  	$syns = strtolower($syns);
  	$query = mysql_query("SELECT 1 FROM ".$syns, $link);
		$num_rows = mysql_num_rows($query);
		$result = mysqli_query($con,"SELECT * from ".$syns);
		while($row = mysqli_fetch_array($result))
		{
		$result2 = mysqli_query($con2,"SELECT * from SECOND_TABLE WHERE PID = ".$row['PID']);
		$row2 = mysqli_fetch_array($result2);
		if(!array_key_exists($row['PID'],$pageranks))
		{
			$titles[$row2['PID']] = $row2['PAPER_NAME'];
			$pageranks[$row['PID']] = 0;
		}
			$pageranks[$row['PID']] = $pageranks[$row['PID']] + PageRank('Title_c',$row['Title_c'],$weight/4);
			$pageranks[$row['PID']] = $pageranks[$row['PID']] + PageRank('Author_c',$row['Author_c'],$weight/8);
			$pageranks[$row['PID']] = $pageranks[$row['PID']] + PageRank('Abstract_c',$row['Abstract_c'],$weight/2);
			$pageranks[$row['PID']] = $pageranks[$row['PID']] + PageRank('Keyword_c',$row['Keyword_c'],$weight/2);
			$pageranks[$row['PID']] = $pageranks[$row['PID']] + PageRank('Citation',$row2['CITATION_COUNT'],$weight/4);
		}
	}
}


#print_r($pageranks);
#echo "<br><br>";
#print_r($titles);
arsort($pageranks);
arsort($citations);
$index = 0;
#print_r($citations);
foreach($citations as $papid => $val)
{
  $authors[$index] = $authors2[$papid];
  $index = $index + 1;
}
#print_r($authors2);
#echo "<br><br>";
echo "<html>";
$index = 0;
foreach($pageranks as $res => $val)
{
$pdfpath = "PDFS/".$res.".pdf";
echo '<a href="'.$pdfpath.'">'.$titles[$res].'</a><br><br>';
$index = $index + 1;
}
echo"<br>--------------------------------------------------Authors-----------------------------------------------------------------------<br><br>";
$index = 0;
foreach($pageranks as $author)
{
$authorpath = "brain.php?query=".$authors[$index];
echo '<a href="'.$authorpath.'">'.$authors[$index].'</a><br><br>';
$index = $index + 1;
}
echo "</html>";
?>
