 <div class="row">
    <div class="col-lg-12">
        <h1 class="page-header"><center>Kumpulan Artikel</center></h1>
    </div>
</div>
<?php 
include "../includes/config.php";
$query = mysqli_query($conn, "SELECT * FROM post ORDER BY tanggal DESC");
 ?>
<div class="panel-body">
                <div class="table-responsive">
                    <table class="table table-striped table-bordered table-hover" id="dataTables-example">
                        <thead>
                            <tr>
                                <th>Judul Artikel</th>
                                <th>Tanggal</th>
                                <th>Isi Artikel</th>
                                <th>Lebih Lanjut</th>

                            </tr>
                        </thead>
                        <tbody>
                            <?php if(mysqli_num_rows($query)){?>
                                <?php while($artikel = mysqli_fetch_array($query)){ ?>
                            <tr>
                                <td><?php echo $artikel["Judul"]; ?></td>
                                <td><?php echo $artikel["tanggal"]; ?></td>
                                <td><?php echo substr($artikel['artikel'], 0, 240); ?></td>
                                <td class="center"><a href="index.php?info-detail=<?php echo $artikel["id_artikel"] ?>" class="btn btn-primary btn-xs" type="button">Lebih Lanjut</a></td>
                            </tr>
                        <?php } ?>
                        <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>