
<?php
    include("../includes/config.php");
?>
<?php 
error_reporting(E_ALL ^ (E_NOTICE | E_WARNING));
 ?>
<?php
    if (!isset($_POST['button']))
    {
?>
<div class="row">
    <div class="col-lg-12">
        <h1>Konsultasi</h1>
    </div>
</div>
<form name="form1" method="post" action=""><br>
  <table align="center" width="750" border="1" align="center" class="table table-striped table-hover table-bordered">
  <tr>
    <td width="786" id="ignore"><center><strong>Pilihlah Gejala Yang Terjadi Pada diri Anda</strong></center></td>
  <?php
    $q = mysqli_query($conn, "select * from t_gejala ORDER BY kode_gejala");
    while ($r = mysqli_fetch_array($q))
    {
    ?>
    <tr>
      <td width="786">
        <input id="gejala<?php echo $r['kode_gejala']; ?>" name="gejala<?php echo $r['kode_gejala']; ?>" type="checkbox" value="true">
        <?php echo $r['nm_gejala']; ?><br/>
        </td>
    </tr>
    <?php } ?>
    <tr>
      <td><input type="submit" name="button" value="Proses"></td>
    </tr>
  </table>
  <br>
</form>

  <?php
  }
  else 
  {
    echo "<div class='row'><h2>Data Hasil Gejala Yang dipilih :</h2>";
    $perintah = "SELECT * from t_gejala";
    $minta =mysqli_query($conn,$perintah);
    $sql = '';
    $i = 0;
    $perintaha = "SELECT * from t_gejala";
    $mintaa =mysqli_query($conn,$perintaha);
    $sqla = '';
    $i = 0;
    //mengecek semua chekbox gejala
    while($hs=mysqli_fetch_array($minta))
    {
        //jika gejala dipilih
        //menyusun daftar gejala misal '1','2','3' dst utk dipakai di query
        if ($_POST['gejala'.$hs['kode_gejala']] == 'true')
        {
            if ($sql == '')
            {
                $sql = "'$hs[kode_gejala]'";
            }
            else
            {
                $sql = $sql.",'$hs[kode_gejala]'";
            }
        }
        $i++;
    }
    while($hsa=mysqli_fetch_array($mintaa))
    {
        //jika gejala dipilih
        //menyusun daftar gejala misal '1','2','3' dst utk dipakai di query
        if ($_POST['gejala'.$hsa['kode_gejala']] == 'true')
        {
            if ($sqla == '')
            {
                $sqla = "'$hsa[kode_gejala]'";
            }
            else
            {
                $sqla = $sqla.",'$hsa[kode_gejala]'";
            }
        }
        $i++;

    }

    empty($daftar_penyakit);
    empty($daftar_cf);
    if ($sql != '')
    {
        //mencari id_penyakit di tabel pengetahuan yang gejalanya dipilih
        $perintah = "SELECT kode_penyakit FROM t_diagcf WHERE kode_gejala IN ($sql) GROUP BY kode_penyakit ORDER BY kode_penyakit";
        //echo "<br/>".$perintah."<br/>";
        $minta =mysqli_query($conn,$perintah);
        $kode_penyakit_terbesar = '';
        $nm_penyakit_terbesar = '';
        
        $c = 0;
        while($hs=mysqli_fetch_array($minta))
        {
            //memproses id penyakit satu persatu
            $kode_penyakit = $hs['kode_penyakit'];
            $qry = mysqli_query($conn,"SELECT * FROM t_penyakit WHERE kode_penyakit = '$kode_penyakit'");
            $dt = mysqli_fetch_array($qry);
            $nm_penyakit = $dt['nm_penyakit'];
            $daftar_penyakit[$c] = $hs['kode_penyakit'];
            
            //mencari gejala yang mempunyai id penyakit tersebut, agar bisa menghitung CF dari MB dan MD nya
            $p = "SELECT kode_penyakit, mb, md, kode_gejala FROM t_diagcf WHERE kode_gejala IN ($sql) AND kode_penyakit = '$kode_penyakit'";
            //echo $p.'<br/>';
            $m =mysqli_query($conn,$p);
            //mencari jumlah gejala yang ditemukan
            $jml = mysqli_num_rows($m);
            //jika gejalanya 1 langsung ketemu CF nya
            
            if ($jml == 1)
            {
                $h=mysqli_fetch_array($m);
                $mb = $h['mb'];
                $md = $h['md'];
                $cf = $mb * $md;
                $hasil = $cf * 100;
                $daftar_cf[$c] = $cf;
                //cek apakah penyakit ini adalah penyakit dgn CF terbesar ?
                if (($kode_penyakit_terbesar == '') || ($cf_terbesar < $hasil))
                {
                    $cf_terbesar = $hasil;
                    $kode_penyakit_terbesar = $kode_penyakit;
                    $nm_penyakit_terbesar = $nm_penyakit;
                }
                
            }
            //jika gejala lebih dari satu harus diproses semua gejala
            else if ($jml > 1)
            {
                $i = 1;
                //proses gejala satu persatu
                while($h=mysqli_fetch_array($m))
                {
                    
                    //pada gejala yang pertama masukkan MB dan MD menjadi MBlama dan MDlama
                    if ($i == 1)
                    {
                        $mblama = $h['mb'];
                        $mdlama = $h['md'];
                        $cflama = $mblama * $mdlama;
                    }
                    //pada gejala yang nomor dua masukkan MB dan MD menjadi MBbaru dan MB baru kemudian hitung MBsementara dan MDsementara
                    else if ($i == 2)
                    {
                        $mbbaru = $h['mb'];
                        $mdbaru = $h['md'];
                        $cfbaru = $mbbaru * $mdbaru;
                        $cfsementara = $cflama + ($cfbaru * (1 - $cflama));
                        
                        //jika jumlah gejala cuma dua maka CF ketemu
                        if ($jml == 2)
                        {
                            $mb = $mbsementara;
                            $md = $mdsementara;
                            $cfhasil = $cfsementara *100;
                            
                            $daftar_cf[$c] = $cfhasil;
                            //cek apakah penyakit ini adalah penyakit dgn CF terbesar ?
                            if (($kode_penyakit_terbesar == '') || ($cf_terbesar < $cfhasil))
                            {
                                $cf_terbesar = $cfhasil;
                                $kode_penyakit_terbesar = $kode_penyakit;
                                $nm_penyakit_terbesar = $nm_penyakit;
                            }
                        }
                    }
                    //pada gejala yang ke 3 dst proses MBsementara dan MDsementara menjadi MBlama dan MDlama
                    //MB dan MD menjadi MBbaru dan MDbaru
                    //hitung MBsementara dan MD sementara yg sekarang
                    else if ($i >= 3)
                    {
                        $mblama = $mbsementara;
                        $mdlama = $mdsementara;
                        
                        $mbbaru = $h['mb'];
                        $mdbaru = $h['md'];
                        
                        $cfakhir = $mbbaru * $mdbaru;
                        $cfhasil = $cfsementara;
                        $cftotal = $cfhasil + ($cfakhir * (1 - $cfhasil));
                        //jika ini adalah gejala terakhir berarti CF ketemu
                        if ($jml == $i)
                        {
                            $mb = $mbsementara;
                            $md = $mdsementara;
                            $cf = $mb - $md;
                            $cfhasil = $mbbaru * $mdbaru;
                            $hasilakhir = $cftotal * 100;
                            $daftar_cf[$c] = $cfhasil;
                            //cek apakah penyakit ini adalah penyakit dgn CF terbesar ?
                            if (($kode_penyakit_terbesar == '') || ($cf_terbesar < $hasilakhir))
                            {
                                $cf_terbesar = $hasilakhir;
                                $kode_penyakit_terbesar = $kode_penyakit;
                                $nm_penyakit_terbesar = $nm_penyakit;
                            }
                        }
                    }
                    $i++;
                }
            }
            $c++;
        }     
    }

    empty($daftar_penyakita);
    empty($daftar_tb);
    if ($sqla != '')
    {
        //mencari id_penyakit di tabel pengetahuan yang gejalanya dipilih
        $perintaha = "SELECT kode_penyakit FROM t_diagcf WHERE kode_gejala IN ($sqla) GROUP BY kode_penyakit ORDER BY kode_penyakit";
        //echo "<br/>".$perintah."<br/>";
        $mintaa = mysqli_query($conn,$perintaha);
        $kodet_penyakit_terbesar = '';
        $nmt_penyakit_terbesar = '';
        $b = 0;
        while($hsa=mysqli_fetch_array($mintaa))
        {
            //memproses id penyakit satu persatu
            $kodet_penyakit = $hsa['kode_penyakit'];
            $qryt = mysqli_query($conn,"SELECT * FROM t_penyakit WHERE kode_penyakit = '$kodet_penyakit'");
            $dtt = mysqli_fetch_array($qryt);
            $nmt_penyakit = $dtt['nm_penyakit'];
            $daftar_penyakita[$b] = $hsa['kode_penyakit'];
            //mencari gejala yang mempunyai id penyakit tersebut, agar bisa menghitung CF dari MB dan MD nya
            $ptb = "SELECT kode_penyakit, mb, md, kode_gejala FROM t_diagcf WHERE kode_gejala IN ($sqla) AND kode_penyakit = '$kodet_penyakit'";
            //echo $p.'<br/>';
            $mtb =mysqli_query($conn,$ptb);
            //mencari jumlah gejala yang ditemukan
            $jmlt = mysqli_num_rows($mtb);
            //jika gejalanya 1 langsung ketemu CF nya
            if ($jmlt == 1)
            {
                $htb=mysqli_fetch_array($mtb);
                $mbt = $htb['mb'];
                $tb = $mbt * $mbt;
                $hasilt = $tb * 100;
                $daftar_tb[$b] = $tb;
                //cek apakah penyakit ini adalah penyakit dgn CF terbesar ?
                if (($kodet_penyakit_terbesar == '') || ($tb_terbesar < $hasilt))
                {
                    $tb_terbesar = $hasilt;
                    $kodet_penyakit_terbesar = $kodet_penyakit;
                    $nmt_penyakit_terbesar = $nmt_penyakit;
                }
            }

             else if ($jmlt > 1)
            {
                $i = 1;
                //proses gejala satu persatu
                while($htb=mysqli_fetch_array($mtb))
                {
                    if ($i == 1)
                    {
                        $mbtn = $htb['mb'];
                        $tbn = $mbtn;
                        $daftar_tb[$b] = $tb;
                    }
                    //pada gejala yang pertama masukkan MB dan MD menjadi MBlama dan MDlama

                    //pada gejala yang nomor dua masukkan MB dan MD menjadi MBbaru dan MB baru kemudian hitung MBsementara dan MDsementara
                    else if ($i == 2)
                    {
                        $mbtne = $htb['mb']; 
                        $xbt = $mbtn + $mbtne;
                        $ybt = $mbtne / $xbt;
                        $ybtn = $mbtn / $xbt;
                        $wbt = ($mbtne * $ybt);
                        $wbtn = ($mbtn * $ybtn);
                        $zbt = $wbt + $wbtn;
                        $vbt = $wbt / $zbt;
                        $vbtn = $wbtn / $zbt;
                        $tbt = ($vbt * $mbtn);
                        $tbtn = ($vbtn * $mbtne);
                        $ubt = $tbt + $tbtn;
                        //jika jumlah gejala cuma dua maka CF ketemu
                        if ($jmlt == 2)
                        {
                            $mbtb = $mbtsementara;
                            $tbtotal = $ubt *100;

                            $daftar_tb[$b] = $tbtotal;
                            //cek apakah penyakit ini adalah penyakit dgn CF terbesar ?
                            if (($kodet_penyakit_terbesar == '') || ($tb_terbesar < $tbtotal))
                            {
                                $tb_terbesar = $tbtotal;
                                $kodet_penyakit_terbesar = $kodet_penyakit;
                                $nmt_penyakit_terbesar = $nmt_penyakit;
                            }
                        }
                    }
                    //pada gejala yang ke 3 dst proses MBsementara dan MDsementara menjadi MBlama dan MDlama
                    //MB dan MD menjadi MBbaru dan MDbaru
                    //hitung MBsementara dan MD sementara yg sekarang
                    else if ($i == 3)
                    {
                        $mbtnew = $htb['mb'];
                        $xbtt = $mbtnew + $xbt;
                        $ybttne = $mbtnew / $xbtt;
                        $ybtt = $mbtne / $xbtt;
                        $ybttn = $mbtn / $xbtt;
                        $wbtt = ($mbtne * $ybtt);
                        $wbttn = ($mbtn * $ybttn);
                        $wbttne = ($mbtnew * $ybttne);
                        $zbtt = $wbtt + $wbttn + $wbttne;
                        $vbtt = $wbtt / $zbtt;
                        $vbttn = $wbttn / $zbtt;
                        $vbttne = $wbttne / $zbtt;
                        $tbtt = ($vbtt * $mbtne);
                        $tbttn = ($vbttn * $mbtn);
                        $tbttne = ($vbttne * $mbtnew);
                        $ubtt = $tbtt + $tbttn + $tbttne;
                        //jika jumlah gejala cuma dua maka CF ketemu
                        if ($jmlt == 3)
                        {
                        $tbhasilakhir = $ubtt *100; 
                        $daftar_tb[$b] = $ubtt;
                            //cek apakah penyakit ini adalah penyakit dgn CF terbesar ?
                            if (($kodet_penyakit_terbesar == '') || ($tb_terbesar < $tbhasilakhir))
                            {
                                $tb_terbesar = $tbhasilakhir;
                                $kodet_penyakit_terbesar = $kodet_penyakit;
                                $nmt_penyakit_terbesar = $nmt_penyakit;
                            }
                        }
                    }

                    else if ($i == 4)
                    {
                        $mbtb = $htb['mb'];
                        $xbttb = $mbtb + $xbtt;
                        $ybttb = $mbtne / $xbttb;
                        $ybttnb = $mbtn / $xbttb;
                        $ybttneb = $mbtnew / $xbttb;
                        $ybttneba = $mbtb / $xbttb;
                        $wbttb = ($mbtne * $ybttb);
                        $wbttnb = ($mbtn * $ybttnb);
                        $wbttneb = ($mbtnew * $ybttneb);
                        $wbttneba = ($mbtb * $ybttneba);
                        $zbttb = $wbttb + $wbttnb + $wbttneb + $wbttneba;
                        $vbttb = $wbttb / $zbttb;
                        $vbttnb = $wbttnb / $zbttb;
                        $vbttneb = $wbttneb / $zbttb;
                        $vbttneba = $wbttneba / $zbttb;
                        $tbttb = ($vbttb * $mbtne);
                        $tbttnb = ($vbttnb * $mbtn);
                        $tbttneb = ($vbttneb * $mbtnew);
                        $tbttneba = ($vbttneba * $mbtb);
                        $ubttb = $tbttb + $tbttnb + $tbttneb + $tbttneba;
                        
                        //jika jumlah gejala cuma dua maka CF ketemu
                        if ($jmlt == 4)
                        {
                        $tbhsl = $ubttb *100; 
                        $daftar_tb[$b] = $ubttb;
                            //cek apakah penyakit ini adalah penyakit dgn CF terbesar ?
                            if (($kodet_penyakit_terbesar == '') || ($tb_terbesar < $tbhsl))
                            {
                                $tb_terbesar = $tbhsl;
                                $kodet_penyakit_terbesar = $kodet_penyakit;
                                $nmt_penyakit_terbesar = $nmt_penyakit;
                            }
                        }
                    }

                    else if ($i == 5)
                    {
                        $mbtba = $htb['mb'];
                        $xbttba = $mbtb + $xbttb;
                        $ybttba = $mbtne / $xbttba;
                        $ybttnba = $mbtn / $xbttba;
                        $ybttneba = $mbtnew / $xbttba;
                        $ybttnebar = $mbtb / $xbttba;
                        $ybttnewbar = $mbtba / $xbttba;
                        $wbttba = ($mbtne * $ybttba);
                        $wbttnba = ($mbtn * $ybttnba);
                        $wbttneba = ($mbtnew * $ybttneba);
                        $wbttnebar = ($mbtb * $ybttnebar);
                        $wbttnewbar = ($mbtba * $ybttnewbar);
                        $zbttba = $wbttba + $wbttnba + $wbttneba + $wbttnebar + $wbttnewbar;
                        $vbttba = $wbttba / $zbttba;
                        $vbttnba = $wbttnba / $zbttba;
                        $vbttneba = $wbttneba / $zbttba;
                        $vbttnebar = $wbttnebar / $zbttba;
                        $vbttnewbar = $wbttnewbar / $zbttba;
                        $tbttba = ($vbttba * $mbtne);
                        $tbttnba = ($vbttnba * $mbtn);
                        $tbttneba = ($vbttneba * $mbtnew);
                        $tbttnebar = ($vbttnebar * $mbtb);
                        $tbttnewbar = ($vbttnewbar * $mbtba);
                        $ubttba = $tbttba + $tbttnba + $tbttneba + $tbttnebar +$tbtnewbar;
                        
                        //jika jumlah gejala cuma dua maka CF ketemu
                        if ($jmlt == 5)
                        {
                        $tbhsla = $ubttba *100; 
                        $daftar_tb[$b] = $ubttba;
                            //cek apakah penyakit ini adalah penyakit dgn CF terbesar ?
                            if (($kodet_penyakit_terbesar == '') || ($tb_terbesar < $tbhsla))
                            {
                                $tb_terbesar = $tbhsla;
                                $kodet_penyakit_terbesar = $kodet_penyakit;
                                $nmt_penyakit_terbesar = $nmt_penyakit;
                            }
                        }
                    }
                     else if ($i == 6)
                    {
                        $mbtbar = $htb['mb'];
                        $xbttbar = $mbtb + $xbttba;
                        $ybttbar = $mbtne / $xbttbar;
                        $ybttnbar = $mbtn / $xbttbar;
                        $ybttnebarr = $mbtnew / $xbttbar;
                        $ybttnebaru = $mbtb / $xbttbar;
                        $ybttnewbaru = $mbtba / $xbttbar;
                        $ybttnewbarua = $mbtbar / $xbttbar;
                        $wbttbar = ($mbtne * $ybttbar);
                        $wbttnbar = ($mbtn * $ybttnbar);
                        $wbttnebarr = ($mbtnew * $ybttnebarr);
                        $wbttnebaru = ($mbtb * $ybttnebaru);
                        $wbttnewbaru = ($mbtba * $ybttnewbaru);
                        $wbttnewbarua = ($mbtbar * $ybttnewbarua);
                        $zbttbar = $wbttbar + $wbttnbar + $wbttnebarr + $wbttnebaru + $wbttnewbaru + $wbttnewbarua;
                        $vbttbar = $wbttbar / $zbttbar;
                        $vbttnbar = $wbttnbar / $zbttbar;
                        $vbttnebarr = $wbttnebarr / $zbttbar;
                        $vbttnebaru = $wbttnebaru / $zbttbar;
                        $vbttnewbaru = $wbttnewbaru / $zbttbar;
                        $vbttnewbarua = $wbttnewbarua / $zbttbar;
                        $tbttbar = ($vbttbar * $mbtne);
                        $tbttnbar = ($vbttnbar * $mbtn);
                        $tbttnebar = ($vbttnebar * $mbtnew);
                        $tbttnebarr = ($vbttnebarr * $mbtb);
                        $tbttnewbaru = ($vbttnewbaru * $mbtba);
                        $tbttnewbarua = ($vbttnewbarua * $mbtbar);
                        $ubttbar = $tbttbar + $tbttnbar + $tbttnebar + $tbttnebarr +$tbtnewbaru;
                        //jika ini adalah gejala terakhir berarti CF ketemu
                        if ($jmlt == 6)
                        {
                            $tbhslar = $ubttbar *100; 
                            $daftar_tb[$b] = $ubttbar;    
                            //cek apakah penyakit ini adalah penyakit dgn CF terbesar ?
                            if (($kodet_penyakit_terbesar == '') || ($tb_terbesar < $tbhslar))
                            {
                                $tb_terbesar = $tbhslar;
                                $kodet_penyakit_terbesar = $kodet_penyakit;
                                $nmt_penyakit_terbesar = $nmt_penyakit;
                            }
                        }
                    }
                    else if ($i == 7)
                    {
                        $mbts = $htb['mb'];
                        $xbts = $mbttb + $xbttbar;
                        $ybts = $mbtne / $xbts;
                        $ybtsm = $mbtn / $xbts;
                        $ybtsmn = $mbtnew / $xbts;
                        $ybtsmnt= $mbtb / $xbts;
                        $ybtsmntr= $mbtba / $xbts;
                        $ybtsmntra = $mbtbar / $xbts;
                        $ybtsemntra = $mbttb / $xbts;
                        $wbts = ($mbtne * $ybts);
                        $wbtsm = ($mbtn * $ybtsm);
                        $wbtsmn = ($mbtnew * $ybtsmn);
                        $wbtsmnt = ($mbtb * $ybtsmnt);
                        $wbtsmntr = ($mbtba * $ybtsmntr);
                        $wbtsmntra = ($mbtbar * $ybtsmntra);
                        $wbtsemntra = ($mbts * $ybtsemntra);
                        $zbts = $wbts + $wbtsm + $wbtsmn + $wbtsmnt + $wbtsmntr + $wbtsmntra + $wbtsemntra;
                        $vbts = $wbts / $zbts;
                        $vbtsm = $wbtsm / $zbts;
                        $vbtsmn = $wbtsmn / $zbts;
                        $vbtsmnt = $wbtsmnt / $zbts;
                        $vbtsmntr = $wbtsmntr / $zbts;
                        $vbtsmntra = $wbtsmntra / $zbts;
                        $vbtsemntra = $wbtsemntra / $zbts;
                        $tbts = ($vbts * $mbtne);
                        $tbtsm = ($vbtsm * $mbtn);
                        $tbtsmn = ($vbtsmn * $mbtnew);
                        $tbtsmnt = ($vbtsmnt * $mbtb);
                        $tbtsmntr = ($vbtsmntr * $mbtba);
                        $tbtsmntra = ($vbtsmntra * $mbtbar);
                        $tbtsemntra = ($vbtsemntra * $mbts);
                        $ubts = $tbts + $tbtsm + $tbtsmn + $tbtsmnt + $tbtsmntr + $tbtsmntra + $tbtsemntra;
                        //jika ini adalah gejala terakhir berarti CF ketemu
                        if ($jmlt == 7)
                        {
                            $tbs = $ubts *100; 
                            $daftar_tb[$b] = $ubts;    
                            //cek apakah penyakit ini adalah penyakit dgn CF terbesar ?
                            if (($kodet_penyakit_terbesar == '') || ($tb_terbesar < $tbs))
                            {
                                $tb_terbesar = $tbs;
                                $kodet_penyakit_terbesar = $kodet_penyakit;
                                $nmt_penyakit_terbesar = $nmt_penyakit;
                            }
                        }
                    }
                    else if ($i == 8)
                    {
                        $mbtrs = $htb['mb'];
                        $xbtrs = $mbtrs + $xbts;
                        $ybtrs = $mbtne / $xbtrs;
                        $ybtrsm = $mbtn / $xbtrs;
                        $ybtrsmn = $mbtnew / $xbtrs;
                        $ybtrsmnt= $mbtb / $xbtrs;
                        $ybtrsmntr= $mbtba / $xbtrs;
                        $ybtrsmntra = $mbtbar / $xbtrs;
                        $ybtrsemntra = $mbttb / $xbtrs;
                        $ybtrsemntara = $mbtrs / $xbtrs;
                        $wbtrs = ($mbtne * $ybtrs);
                        $wbtrsm = ($mbtn * $ybtrsm);
                        $wbtrsmn = ($mbtnew * $ybtrsmn);
                        $wbtrsmnt = ($mbtb * $ybtrsmnt);
                        $wbtrsmntr = ($mbtba * $ybtrsmntr);
                        $wbtrsmntra = ($mbtbar * $ybtrsmntra);
                        $wbtrsemntra = ($mbttb * $ybtrsemntra);
                        $wbtrsemntara = ($mbtrs * $ybtrsemntara);
                        $zbtrs = $wbtrs + $wbtrsm + $wbtrsmn + $wbtrsmnt + $wbtrsmntr + $wbtrsmntra + $wbtrsemntra + $wbtrsemntara;
                        $vbtrs = $wbtrs / $zbtrs;
                        $vbtrsm = $wbtrsm / $zbtrs;
                        $vbtrsmn = $wbtrsmn / $zbtrs;
                        $vbtrsmnt = $wbtrsmnt / $zbtrs;
                        $vbtrsmntr = $wbtrsmntr / $zbtrs;
                        $vbtrsmntra = $wbtrsmntra / $zbtrs;
                        $vbtrsemntra = $wbtrsemntra / $zbtrs;
                        $vbtrsemntara = $wbtrsemntara / $zbtrs;
                        $tbtrs = ($vbtrs * $mbtne);
                        $tbtrsm = ($vbtrsm * $mbtn);
                        $tbtrsmn = ($vbtrsmn * $mbtnew);
                        $tbtrsmnt = ($vbtrsmnt * $mbtb);
                        $tbtrsmntr = ($vbtrsmntr * $mbtba);
                        $tbtrsmntra = ($vbtrsmntra * $mbtbar);
                        $tbtrsemntra = ($vbtrsemntra * $mbts);
                        $tbtrsemntara = ($vbtrsemntara * $mbtrs);
                        $ubtrs = $tbtrs + $tbtrsm + $tbtrsmn + $tbtrsmnt + $tbtrsmntr + $tbtrsmntra + $tbtrsemntra;
                        //jika ini adalah gejala terakhir berarti CF ketemu
                        if ($jmlt == 8)
                        {
                            $tbrs = $ubtrs *100; 
                            $daftar_tb[$b] = $ubtrs;    
                            //cek apakah penyakit ini adalah penyakit dgn CF terbesar ?
                            if (($kodet_penyakit_terbesar == '') || ($tb_terbesar < $tbrs))
                            {
                                $tb_terbesar = $tbrs;
                                $kodet_penyakit_terbesar = $kodet_penyakit;
                                $nmt_penyakit_terbesar = $nmt_penyakit;
                            }
                        }
                    }
                    else if ($i == 9)
                    {
                        $mbtrus = $htb['mb'];
                        $xbtrus = $mbtrus + $xbtrs;
                        $ybtrus = $mbtne / $xbtrus;
                        $ybtrusm = $mbtn / $xbtrus;
                        $ybtrusmn = $mbtnew / $xbtrus;
                        $ybtrusmnt= $mbtb / $xbtrus;
                        $ybtrusmntr= $mbtba / $xbtrus;
                        $ybtrusmntra = $mbtbar / $xbtrus;
                        $ybtrusemntra = $mbts / $xbtrus;
                        $ybtrusemntara = $mbtrs / $xbtrus;
                        $ybtrusementara = $mbtrus / $xbtrus;
                        $wbtrus = ($mbtne * $ybtrus);
                        $wbtrusm = ($mbtn * $ybtrusm);
                        $wbtrusmn = ($mbtnew * $ybtrusmn);
                        $wbtrusmnt = ($mbtb * $ybtrusmnt);
                        $wbtrusmntr = ($mbtba * $ybtrusmntr);
                        $wbtrusmntra = ($mbtbar * $ybtrusmntra);
                        $wbtrusemntra = ($mbts * $ybtrusemntra);
                        $wbtrusemntara = ($mbtrs * $ybtrusemntara);
                        $wbtrusementara = ($mbtrus * $ybtrusementara);
                        $zbtrus = $wbtrus + $wbtrusm + $wbtrusmn + $wbtrusmnt + $wbtrusmntr + $wbtrusmntra + $wbtrusemntra + $wbtrusemntara + $wbtrusementara;
                        $vbtrus = $wbtrus / $zbtrus;
                        $vbtrusm = $wbtrusm / $zbtrus;
                        $vbtrusmn = $wbtrusmn / $zbtrus;
                        $vbtrusmnt = $wbtrusmnt / $zbtrus;
                        $vbtrusmntr = $wbtrusmntr / $zbtrus;
                        $vbtrusmntra = $wbtrusmntra / $zbtrus;
                        $vbtrusemntra = $wbtrusemntra / $zbtrus;
                        $vbtrusemntara = $wbtrusemntara / $zbtrus;
                        $vbtrusementara = $wbtrusementara / $zbtrus;
                        $tbtrus = ($vbtrus * $mbtne);
                        $tbtrusm = ($vbtrusm * $mbtn);
                        $tbtrusmn = ($vbtrusmn * $mbtnew);
                        $tbtrusmnt = ($vbtrusmnt * $mbtb);
                        $tbtrusmntr = ($vbtrusmntr * $mbtba);
                        $tbtrusmntra = ($vbtrusmntra * $mbtbar);
                        $tbtrusemntra = ($vbtrusemntra * $mbts);
                        $tbtrusemntara = ($vbtrusemntara * $mbtrs);
                        $tbtrusementara = ($vbtrusementara * $mbtrus);
                        $ubtrus = $tbtrus + $tbtrusm + $tbtrusmn + $tbtrusmnt + $tbtrusmntr + $tbtrusmntra + $tbtrusemntra + $tbtrusemntara + $tbtrusementara;
                        //jika ini adalah gejala terakhir berarti CF ketemu
                        if ($jmlt == 9)
                        {
                            $tbrus = $ubtrus *100; 
                            $daftar_tb[$b] = $ubtrus;    
                            //cek apakah penyakit ini adalah penyakit dgn CF terbesar ?
                            if (($kodet_penyakit_terbesar == '') || ($tb_terbesar < $tbrus))
                            {
                                $tb_terbesar = $tbrus;
                                $kodet_penyakit_terbesar = $kodet_penyakit;
                                $nmt_penyakit_terbesar = $nmt_penyakit;
                            }
                        }
                    }
                    else if ($i == 10)
                    {
                        $mbtrues = $htb['mb'];
                        $xbtrues = $mbtrues + $xbtrus;
                        $ybtrues = $mbtne / $xbtrues;
                        $ybtruesm = $mbtn / $xbtrues;
                        $ybtruesmn = $mbtnew / $xbtrues;
                        $ybtruesmnt= $mbtb / $xbtrues;
                        $ybtruesmntr= $mbtba / $xbtrues;
                        $ybtruesmntra = $mbtbar / $xbtrues;
                        $ybtruesemntra = $mbts / $xbtrues;
                        $ybtruesemntara = $mbtrs / $xbtrues;
                        $ybtruesementara = $mbtrus / $xbtrues;
                        $ybtrue_sementara = $mbtrues / $xbtrues;
                        $wbtrues = ($mbtne * $ybtrues);
                        $wbtruesm = ($mbtn * $ybtruesm);
                        $wbtruesmn = ($mbtnew * $ybtruesmn);
                        $wbtruesmnt = ($mbtb * $ybtruesmnt);
                        $wbtruesmntr = ($mbtba * $ybtruesmntr);
                        $wbtruesmntra = ($mbtbar * $ybtruesmntra);
                        $wbtruesemntra = ($mbts * $ybtruesemntra);
                        $wbtruesemntara = ($mbtrs * $ybtruesemntara);
                        $wbtruesementara = ($mbtrus * $ybtruesementara);
                        $wbtrue_sementara = ($mbtrues * $ybtrue_sementara);
                        $zbtrues = $wbtrues + $wbtruesm + $wbtruesmn + $wbtruesmnt + $wbtruesmntr + $wbtruesmntra + $wbtruesemntra + $wbtruesemntara + $wbtruesementara + $wbtrue_sementara;
                        $vbtrues = $wbtrues / $zbtrues;
                        $vbtruesm = $wbtruesm / $zbtrues;
                        $vbtruesmn = $wbtruesmn / $zbtrues;
                        $vbtruesmnt = $wbtruesmnt / $zbtrues;
                        $vbtruesmntr = $wbtruesmntr / $zbtrues;
                        $vbtruesmntra = $wbtruesmntra / $zbtrues;
                        $vbtruesemntra = $wbtruesemntra / $zbtrues;
                        $vbtruesemntara = $wbtruesemntara / $zbtrues;
                        $vbtruesementara = $wbtruesementara / $zbtrues;
                        $vbtrue_sementara = $wbtrue_sementara / $zbtrues;
                        $tbtrues = ($vbtrues * $mbtne);
                        $tbtruesm = ($vbtruesm * $mbtn);
                        $tbtruesmn = ($vbtruesmn * $mbtnew);
                        $tbtruesmnt = ($vbtruesmnt * $mbtb);
                        $tbtruesmntr = ($vbtruesmntr * $mbtba);
                        $tbtruesmntra = ($vbtruesmntra * $mbtbar);
                        $tbtruesemntra = ($vbtruesemntra * $mbts);
                        $tbtruesemntara = ($vbtruesemntara * $mbtrs);
                        $tbtruesementara = ($vbtruesementara * $mbtrus);
                        $tbtrue_sementara = ($vbtrue_sementara * $mbtrues);
                        $ubtrues = $tbtrues + $tbtruesm + $tbtruesmn + $tbtruesmnt + $tbtruesmntr + $tbtruesmntra + $tbtruesemntra + $tbtruesemntara + $tbtruesementara + $tbtrue_sementara;
                        //jika ini adalah gejala terakhir berarti CF ketemu
                        if ($jmlt == 10)
                        {
                            $tbrues = $ubtrues *100; 
                            $daftar_tb[$b] = $ubtrues;    
                            //cek apakah penyakit ini adalah penyakit dgn CF terbesar ?
                            if (($kodet_penyakit_terbesar == '') || ($tb_terbesar < $tbrues))
                            {
                                $tb_terbesar = $tbrues;
                                $kodet_penyakit_terbesar = $kodet_penyakit;
                                $nmt_penyakit_terbesar = $nmt_penyakit;
                            }
                        }
                    }
                    $i++;
                }
            }
            $b++;
        }

    }

    //urutkan daftar gejala berdasarkan besar CF
    for ($i = 0; $i < count($daftar_penyakit); $i++)
    {
        for ($j = $i + 1; $j < count($daftar_penyakit); $j++)
        {
            if ($daftar_cf[$j] > $daftar_cf[$i])
            {
                $t = $daftar_cf[$i];
                $daftar_cf[$i] = $daftar_cf[$j];
                $daftar_cf[$j] = $t;

                $t = $daftar_penyakit[$i];
                $daftar_penyakit[$i] = $daftar_penyakit[$j];
                $daftar_penyakit[$j] = $t;
            }
        }
    }
    
    for ($i = 0; $i < count($daftar_penyakita); $i++)
    {
        for ($j = $i + 1; $j < count($daftar_penyakita); $j++)
        {
            if ($daftar_tb[$j] > $daftar_tb[$i])
            {
                $t = $daftar_tb[$i];
                $daftar_tb[$i] = $daftar_tb[$j];
                $daftar_tb[$j] = $t;

                $t = $daftar_penyakita[$i];
                $daftar_penyakita[$i] = $daftar_penyakita[$j];
                $daftar_penyakita[$j] = $t;
            }
        }
    }
    //for ($i = 0; $i < count($daftar_penyakit); $i++)
    //{
    //  echo $daftar_penyakit[$i]."=".$daftar_cf[$i]."<br/>";
    //}

    //menyimpan hasil ke database

    $queryPasien=mysqli_query($conn,"SELECT * FROM loginuser WHERE iduser='$_SESSION[iduser]'"); 
    $dataPasien=mysqli_fetch_array($queryPasien);
    $iduser=$dataPasien["iduser"];
    $simpandata=mysqli_query($conn,"INSERT INTO t_hasil (iduser,kode_penyakit,nm_penyakit,persentasse,tanggal) VALUES ('$iduser','$kode_penyakit_terbesar','$nm_penyakit_terbesar','$cf_terbesar', NOW() ) ");
    $simpandatat=mysqli_query($conn,"INSERT INTO hasil (id_hasil,iduser,kode_penyakit,nm_penyakit,presentase,tanggal) VALUES ('','$iduser','$kodet_penyakit_terbesar','$nmt_penyakit_terbesar','$tb_terbesar', NOW() ) ");

    ?>
    <div class="row">
    <div class="col-lg-12">
        <div class="panel-body">
            <div class="table-responsive">
    
<table width="750" border="1" align="center" class="table table-striped table-hover table-bordered">
    <thead>
        <tr>
            <th>Hasil Konsultasi</td>
        </tr>
    </thead>
    <tbody>
    <tr>
        <td>
            Gejala Yang Dipilih
        </td>
        <td>
            <?php
                $perintah = "SELECT * from t_gejala";
                $minta =mysqli_query($conn,$perintah);
                while($hs=mysqli_fetch_array($minta))
                {
                    if ($_POST['gejala'.$hs['kode_gejala']] == 'true')
                    {
                ?>
                    <?php echo $hs['nm_gejala']; ?> <br />
                <?php
                    }
                }
                ?>
        </td>
    </tr>
    <tr>
        <td>
            Hasil Diagnosa
        </td>
        <?php
                $perintah = "SELECT * from t_penyakit where kode_penyakit = '$kode_penyakit_terbesar'";
                $minta =mysqli_query($conn,$perintah);
                $hs=mysqli_fetch_array($minta);
                //$kode_penyakit_terbesar
                ?>
        <td>
                <?php echo $hs['nm_penyakit']; ?>
        </td>
    </tr>
    <tr>
        <td>
            Presentase Kemungkinan Metode Certainty Factor
        </td>
        <td>
            <?php echo "$cf_terbesar%"; ?>
        </td>
    </tr>
    <tr>
        <td>
            Presentase Kemungkinan Metode Teorema Bayes
        </td>
        <td>
            <?php echo "$tb_terbesar%"; ?>
        </td>
    </tr>
     <tr>
        <td>
            Cara Penanganan 
        </td>
        <?php 
            $perintah = "SELECT * from t_penyakit where kode_penyakit = '$kode_penyakit_terbesar'";
            $minta =mysqli_query($conn,$perintah);
            $hs=mysqli_fetch_array($minta);
         ?>
        <td>
            <?php echo $hs["penanganan"] ?>
        </td>
    </tr>
    </tbody>
</table>
</div>
</div>
    <?php
    }
    ?>