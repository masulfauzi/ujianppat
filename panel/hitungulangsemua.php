<?php
require("../config/config.database.php");
require("../config/config.default.php");
require("../config/config.function.php");
require("../config/functions.crud.php");

$id_mapel = $_GET['id'];

$hasil = mysqli_query($koneksi, "select * from nilai where id_mapel = " . $id_mapel);

while ($siswa = mysqli_fetch_array($hasil)) {
    $idnilai =  $siswa['id_nilai'];
    $nilai = fetch($koneksi, 'nilai', array('id_nilai' => $idnilai));
    $idm = $nilai['id_mapel'];
    $ids = $nilai['id_siswa'];
    $idu = $nilai['id_ujian'];
    $where = array(
        'id_mapel' => $idm,
        'id_siswa' => $ids,
        'id_ujian' => $idu
    );

    $benar = $salah = 0;
    $mapel = fetch($koneksi, 'mapel', array('id_mapel' => $idm));
    $siswa = fetch($koneksi, 'siswa', array('id_siswa' => $ids));
    $ceksoal = select($koneksi, 'soal', array('id_mapel' => $idm, 'jenis' => 1));
    $ceksoalesai = select($koneksi, 'soal', array('id_mapel' => $idm, 'jenis' => 2));

    $arrayjawabesai = array();
    foreach ($ceksoalesai as $getsoalesai) {
        // print_r($ceksoalesai);
        // die();
        $w2 = array(
            'id_siswa' => $ids,
            'id_mapel' => $idm,
            'id_soal' => $getsoalesai['id_soal'],
            'jenis' => 2
        );

        $getjwb2 = fetch($koneksi, 'jawaban', $w2);
        if ($getjwb2) {
            $jawabxx = str_replace("'", "`", $getjwb2['esai']);
            $jawabxx = str_replace("#", ">>", $jawabxx);
            $jawabxx = preg_replace('/[^A-Za-z0-9\@\<\>\$\_\&\-\+\(\)\/\?\!\;\:\`\"\[\]\*\{\}\=\%\~\`\÷\× ]/', '', $jawabxx);
            $arrayjawabesai[$getsoalesai['id_soal']] = $jawabxx;
        } else {
            $arrayjawabesai[$getsoalesai['id_soal']] = 'Tidak Diisi';
        }
    }
    $arrayjawab = array();
    $skor = 0;
    foreach ($ceksoal as $getsoal) {
        // echo "<pre>";
        // print_r($getsoal);
        // die();
        $w = array(
            'id_siswa' => $ids,
            'id_mapel' => $idm,
            'id_soal' => $getsoal['id_soal'],
            'jenis' => 1
        );
        $getjwb = fetch($koneksi, 'jawaban', $w);
        // echo "<pre>";
        // print_r($getjwb);
        // die();
        if ($getjwb) {
            $arrayjawab[$getsoal['id_soal']] = $getjwb['jawaban'];
        } else {
            $arrayjawab[$getsoal['id_soal']] = 'X';
        }
        // ($getjwb['jawaban'] == $getsoal['jawaban']) ? $benar++ : $salah++;

        if ($getjwb['jawaban'] == $getsoal['jawaban']) {
            // die("benar");
            // jika benar
            // bobot soal
            if ($getsoal['kategori'] == 1) {
                $skor += 1;
            } else if ($getsoal['kategori'] == 2) {
                $skor += 2.3;
            } else {
                $skor += 3.5;
            }
        } else {
            if ($arrayjawab[$getsoal['id_soal']] == "X") {
                // die("Tidak dijawab");
            } else {
                // die("salah");
                if ($getsoal['kategori'] == 1) {
                    $skor -= 0.13;
                } else if ($getsoal['kategori'] == 2) {
                    $skor -= 0.3;
                } else {
                    $skor -= 0.67;
                }
            }
        }
    }
    $bagi = $mapel['jml_soal'] / 100;
    $bobot = $mapel['bobot_pg'] / 100;
    // $skor = ($benar / $bagi) * $bobot;
    $data = array(
        'ujian_selesai' => $datetime,
        'jml_benar' => $benar,
        'jml_salah' => $salah,
        'skor' => $skor,
        'total' => $skor,
        'online' => 0,
        'jawaban' => serialize($arrayjawab),
        'jawaban_esai' => serialize($arrayjawabesai)
    );
    $simpan = update($koneksi, 'nilai', $data, $where);
    if ($simpan) {
        echo "ID " . $idnilai . " Berhasil di proses <br>";
    }
    echo mysqli_error($koneksi);
}
