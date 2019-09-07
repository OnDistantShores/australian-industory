<!DOCTYPE HTML>
<html>
<head>
<script>
window.onload = function() {

var data = Array();
<?php
    $labels = array();
    $data = array();

    $victorianBusinesses = file_get_contents("S&McD.csv");
    $rows = str_getcsv($victorianBusinesses, PHP_EOL);
    $isFirst = true;
    foreach ($rows as $row) {
        if (!$isFirst) {
            $rowWithFields = str_getcsv($row, ",");

            if (!in_array($rowWithFields[1], $labels)) {
                $labels[] = $rowWithFields[1];
            }

            if (!isset($data[$rowWithFields[0]])) {
                $data[$rowWithFields[0]] = array();
            }

            $data[$rowWithFields[0]][] = array(
                "label" => $rowWithFields[1],
                "y" => $rowWithFields[2],
            );
        }
        $isFirst = false;
    }

    echo "var labels = Array(\"" . implode("\",\"", $labels) . "\");" . PHP_EOL;

    foreach ($data as $year => $rows) {
        echo "data[$year] = " . json_encode($rows) . PHP_EOL;
    }
?>

var chart = new CanvasJS.Chart("chartContainer", {
	theme: "light2", // "light1", "light2", "dark1", "dark2"
	exportEnabled: true,
	animationEnabled: true,
	title: {
		text: "Australian industries over time"
	},
	data: [{
		type: "pie",
		startAngle: 25,
		toolTipContent: "<b>{label}</b>: {y}%",
		//showInLegend: "true",
		legendText: "{label}",
		indexLabelFontSize: 16,
		indexLabel: "{label} - {y}%",
		dataPoints: data[1915]
	}]
});
chart.render();

}
</script>
</head>
<body>
<div id="chartContainer" style="height: 600px; width: 100%;"></div>
<script src="https://canvasjs.com/assets/script/canvasjs.min.js"></script>
</body>
</html>
