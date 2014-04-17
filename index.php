<!DOCTYPE html>
<html>

    <head>
        <title>Pretraga dijelova sa assemblio.hr na nabava.net</title>
        <link rel="stylesheet" href="stil.css" />
        <meta charset="utf-8" />
        <script src="http://ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js"></script>
    </head>


    <form action="<?php $link = $_POST; ?>" method="post">
        Kopirajte i zaljepite link do vaše konfiguracije.<br />
        Link je oblika <em>"http://assemblio.hr/pc/aior45g4".</em><br />
        <input id="input-link" type="url" name="link" autofocus />
        <input id="input-submit" type="submit" value="Pronađi dijelove!"/>
    </form>

    <?php
    if (!empty($link)) {
        $link = htmlspecialchars($_POST["link"]);
    }

    if (isset($link) &&
            !empty($link)) {

        if (preg_match("/((http:\/\/assemblio.hr\/pc\/)([A-Za-z0-9]){8})/", $link)) {
            $contents = file_get_contents($link);
            $contents = strip_tags($contents);
        } else {
            echo "<div id='sadrzaj'>Pogrešan unos!</div>";
        }

        if (isset($link) &&
                $contents == "") {
            echo "<div id='sadrzaj'>Konfiguracija ne postoji!</div>";
        }

        function dohvati_naziv($contents, $pocetak, $kraj) { //funkcija dohvaca nazive komponenti
            $komponenta_duzina = (($pocetak === "Tvrdi disk:" | $pocetak === "Solid-state disk:") ? 80 : 70);
            $komponenta = strrpos($contents, $pocetak);
            $komponenta = substr($contents, $komponenta + strlen($pocetak) + 1, $komponenta_duzina);
            if ($pocetak == "Tvrdi disk:" |
                    $pocetak == "Solid-state disk:") {
                $komponenta_kraj = strpos($komponenta, $kraj);
                $disk_vel = strpos($komponenta, "-");
                $disk_vel = substr($komponenta, $disk_vel + 2);
            } else {
                $komponenta_kraj = strrpos($komponenta, $kraj);
            }
            $komponenta = substr($komponenta, 0, -strlen($komponenta) + $komponenta_kraj);
            $komponenta = trim($komponenta);
            return ($pocetak === "Tvrdi disk:" | $pocetak === "Solid-state disk:" ? $komponenta . " " . $disk_vel : $komponenta);
        }

//dohvacanje naziva komponenti
        $procesor = dohvati_naziv($contents, "Procesor:", "(");
        $maticna = dohvati_naziv($contents, " ploča:", "(");
        if (dohvati_naziv($contents, " kartica:", " ") === "Integrirana grafička kartica") {
            $graficka = null;
        } else {
            $graficka = dohvati_naziv($contents, " kartica:", " ");
        }
        $memorija = dohvati_naziv($contents, " memorija:", ",");
        $disk = dohvati_naziv($contents, "Tvrdi disk:", ",");
        $ssd = dohvati_naziv($contents, "Solid-state disk:", "-");
        if (substr($ssd, strpos($ssd, "Assemblio"), strlen("Assemblio")) === "Assemblio") { //mali workaround, ako nema ssd-a, funkcija dohvati_naziv() dohvaca pocetak stranice
            $ssd = null;
        }
        $napajanje = dohvati_naziv($contents, "Napajanje:", " ");
        $napajanje_visak = substr($napajanje, strpos($napajanje, ","), strrpos($napajanje, "-") - strpos($napajanje, ",") + 1);
        if (strpos($napajanje, ",") != false) {
            $napajanje = str_replace($napajanje_visak, "", $napajanje);
        }
        $napajanje = str_replace(" - ", " ", $napajanje);

//izrada search stringova 
        $procesor_search = strtr($procesor, " ", "+");
        $maticna_search = strtr($maticna, " ", "+");
        $graficka_search = strtr($graficka, " ", "+");
        $memorija_search = strtr($memorija, " ", "+");
        $disk_search = strtr($disk, " ", "+");
        $ssd_search = strtr($ssd, " ", "+");
        $trans = array(" " => "+", "-" => "");
        $napajanje_search = strtr($napajanje, $trans);

        function dohvati_artikl($search_string) {
            $funkcija = "NABAVA.listaZeljaUpdate('artikl=";
            $komponenta_res = "http://www.nabava.net/search.php?q=" . $search_string;
            if ($komponenta_res != "http://www.nabava.net/search.php?q=") {
                $komponenta_res = file_get_contents($komponenta_res);
            }
            $add_func = strpos($komponenta_res, $funkcija);
            $add_func = substr($komponenta_res, $add_func);
            $add_func_kraj = strpos($add_func, ",");
            $add_func = substr($add_func, strlen($funkcija), $add_func_kraj - strlen($funkcija) - 1);
            return $add_func;
        }

        /*
          var_dump($procesor);
          echo("<br>");
          var_dump($maticna);
          echo("<br>");
          var_dump($graficka);
          echo("<br>");
          var_dump($memorija);
          echo("<br>");
          var_dump($disk);
          echo("<br>");
          var_dump($ssd);
          echo("<br>");
          var_dump($napajanje);
          echo("<br>");
         */

        $procesor = dohvati_artikl($procesor_search);
        $maticna = dohvati_artikl($maticna_search);
        if ($graficka != null) {
            $graficka = dohvati_artikl($graficka_search);
            $graficka_uv = 1;
        }
        $memorija = dohvati_artikl($memorija_search);
        $disk = dohvati_artikl($disk_search);
        if ($ssd != null) {
            $ssd = dohvati_artikl($ssd_search);
            $ssd_uv = 1;
        }
        $napajanje = dohvati_artikl($napajanje_search);

        /*
          var_dump($procesor);
          echo("<br>");
          var_dump($maticna);
          echo("<br>");
          var_dump($graficka);
          echo("<br>");
          var_dump($memorija);
          echo("<br>");
          var_dump($disk);
          echo("<br>");
          var_dump($ssd);
          echo("<br>");
          var_dump($napajanje);
          echo("<br>");
         */

        $finish = 1;
    }
    ?>

    <div id="procesor_frame"></div>
    <div id="maticna_frame"></div>
    <div id="graficka_frame"></div>
    <div id="memorija_frame"></div>
    <div id="disk_frame"></div>
    <div id="ssd_frame"></div>
    <div id="napajanje_frame"></div>
    
    <div id="sadrzaj"></div>

    <script>

        var finish = <?php echo (isset($finish) ? $finish : "0"); ?>;

        if (finish === 1) {
            var url = "http://www.nabava.net/ajax_lista_zelja_update.php?artikl=";

            var procesor = "<?php echo (isset($procesor) ? $procesor : "0") ?>";
            document.getElementById("procesor_frame").innerHTML = "<iframe src='" + url + procesor + "' hidden ></iframe>";

            var maticna = "<?php echo (isset($maticna) ? $maticna : "0") ?>";
            document.getElementById("maticna_frame").innerHTML = "<iframe src='" + url + maticna + "' hidden ></iframe>";

            var graficka = "<?php echo (isset($graficka) && $graficka != null ? $graficka : "0") ?>";
            if (graficka !== 0) {
                document.getElementById("graficka_frame").innerHTML = "<iframe src='" + url + graficka + "' hidden ></iframe>";
            }

            var memorija = "<?php echo (isset($memorija) ? $memorija : "0") ?>";
            document.getElementById("memorija_frame").innerHTML = "<iframe src='" + url + memorija + "' hidden ></iframe>";

            var disk = "<?php echo (isset($disk) ? $disk : "0") ?>";
            document.getElementById("disk_frame").innerHTML = "<iframe src='" + url + disk + "' hidden ></iframe>";

            var ssd = "<?php echo (isset($ssd) && $ssd != null ? $ssd : "0") ?>";
            if (ssd !== 0) {
                document.getElementById("ssd_frame").innerHTML = "<iframe src='" + url + ssd + "' hidden ></iframe>";
            }

            var napajanje = "<?php echo (isset($napajanje) ? $napajanje : "0") ?>";
            document.getElementById("napajanje_frame").innerHTML = "<iframe src='" + url + napajanje + "' hidden ></iframe>";

            var redirect = "http://www.nabava.net/lista_zelja.php";
            document.getElementById("sadrzaj").innerHTML = "<a href='" + redirect + "'>Vidi listu želja</a>";
        }
    </script>



</body>
</html>