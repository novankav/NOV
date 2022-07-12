<?php 
include "../includes/config.php";
if(isset( $_GET["info-detail"])){
    $id_artikel = $_GET['info-detail'];
    $query= mysqli_query($conn, "SELECT * FROM post WHERE id_artikel='$id_artikel' ORDER BY tanggal DESC");
}
 ?>
<div class="row">
    <div class="col-lg-12">
        <?php if(mysqli_num_rows($query)){?>
    <?php while($artikel = mysqli_fetch_array($query)){ ?>
        <div class="row">
            <div class="col-lg-12">
                <h1><?php echo $artikel["Judul"]; ?></h1>
                <p><?php echo $artikel["tanggal"]; ?></p>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-12">
                <?php echo $artikel["artikel"]; ?>
            </div>
        </div>
            
        
        
        
    <?php } ?>
    <?php } ?>
    </div>
</div>
    
