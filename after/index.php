<?php 
ob_start();
session_start();
if(!isset($_SESSION["iduser"]))header("location:../login.php");
include "../includes/config.php";
?>
<!DOCTYPE html>
<html>
<?php include("include/head.php") ?>
<body>
    <div id="wrapper">
        <?php include("include/header.php") ?>
        <div id="page-wrapper">
            <?php
            if (isset($_GET["diagnosa"])) include("page/blog/diagnosa.php");
            else if (isset($_GET["history"])) include("page/blog/history.php");
            else if (isset($_GET["historytb"])) include("page/blog/historytb.php");
            else if (isset($_GET["info"]))  include("page/blog/info.php");
            else if (isset($_GET["info-detail"])) include("page/blog/info-detail.php");
            else if (isset($_GET["page"]))  include("page/blog/page.php");
            else include("page/home/index.php");
            ?>
        </div>
    </div>
    <?php include("include/footer.php") ?>
</body>
</html>
<?php 
mysqli_close($conn);
ob_end_flush();

 ?>