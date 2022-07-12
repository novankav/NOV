<div class="row">
    <div class="col-lg-12">
        <h1>HISTORY</h1>
    </div>
</div>
<div class="row">
  <div class="col-lg-12">
    <?php 

      $bisa = mysqli_query($conn,"SELECT hasil.id_hasil, hasil.tanggal, hasil.presentase, hasil.nm_penyakit, loginuser.iduser
                FROM hasil, loginuser
                WHERE loginuser.iduser=hasil.iduser
                AND loginuser.iduser = '".$_SESSION["iduser"]."'
                ORDER BY tanggal DESC");
     ?>

      </div>
             <div class="panel-body">
                <div class="table-responsive">
                    <table class="table table-striped table-bordered table-hover" id="dataTables-example">
                        <thead>    
                            <th colspan="4"> <h3>  Metode Teorema Bayes  </h3>   
                            <tr>
                                <th>No</th>
                                <th>Tanggal</th>
                                <th>Nama Penyakit</th>
                                <th>Presentase </th>  
                            </tr>
                            </th>
                        </thead>
                             
                            <?php 
                                $no=0;
                                if(mysqli_num_rows($bisa)){?>
                                    <?php while($row = mysqli_fetch_array($bisa)){ 
                                      $no++?>
                                <tr>
                                    <td><?php echo $no ?></td>
                                    <td><?php echo $row["tanggal"] ?></td>
                                    <td><?php echo $row["nm_penyakit"] ?></td>
                                    <td><?php echo $row["presentase"] ?></td>
                                    <?php } ?>
                                <?php } ?>
                        
                    </table>
                </div>
            </div>