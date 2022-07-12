<div class="row">
    <div class="col-lg-12">
        <h1>HISTORY</h1>
    </div>
</div>
<div class="row">
  <div class="col-lg-12">
    <?php 


      include "../includes/config.php";
      $query = mysqli_query($conn,"SELECT t_hasil.id_hasil, t_hasil.tanggal, t_hasil.persentasse, t_hasil.nm_penyakit, loginuser.iduser
                FROM t_hasil, loginuser
                WHERE loginuser.iduser=t_hasil.iduser
                AND loginuser.iduser = '".$_SESSION["iduser"]."'
                ORDER BY tanggal DESC");

     ?>

            </div>
            <div class="panel-body">
                <div class="table-responsive">
                    <table class="table table-striped table-bordered table-hover" id="dataTables-example">
                        <thead>    
                            <th colspan="4"> <h3>  Metode Certainty Factor  </h3>   
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
                                if(mysqli_num_rows($query)){?>
                                    <?php while($row = mysqli_fetch_array($query)){ 
                                      $no++?>
                                <tr>
                                    <td><?php echo $no ?></td>
                                    <td><?php echo $row["tanggal"] ?></td>
                                    <td><?php echo $row["nm_penyakit"] ?></td>
                                    <td><?php echo $row["persentasse"] ?></td>
                                    <?php } ?>
                                <?php } ?>
                        
                    </table>
                </div>
            </div>