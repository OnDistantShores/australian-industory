<?php

    function compareBySize($a, $b){
        if ($a[2] == $b[2]) {
            return 0;
        }
        return ($a[2] > $b[2]) ? -1 : 1;
    }

    $data = array();

    $victorianBusinessesRaw = file_get_contents("S&McD.csv");

    // Index the data by year
    $rows = str_getcsv($victorianBusinessesRaw, PHP_EOL);
    $isFirst = true;
    foreach ($rows as $row) {
        if (!$isFirst) {
            $rowWithFields = str_getcsv($row, ",");

            if (!isset($data[$rowWithFields[0]])) {
                $data[$rowWithFields[0]] = array();
            }
            $data[$rowWithFields[0]][] = $rowWithFields;
        }
        $isFirst = false;
    }

    $years = array_keys($data);

    $labels = array();
    $datasets = array();

    $setIndustryOrder = null;

    foreach ($data as $year => $rows) {
        $labels[$year] = array();
        $datasets[$year] = array();

        $rowsIndexedByIndustry = array();
        foreach ($rows as $row) {
            $rowsIndexedByIndustry[$row[1]] = $row;
        }

        if (!$setIndustryOrder) {
            // Order the rows
            uasort($rowsIndexedByIndustry, "compareBySize");

            // Save this order to consistently apply henceforth
            $setIndustryOrder = array_keys($rowsIndexedByIndustry);
        }

        foreach ($setIndustryOrder as $industry) {
            $row = $rowsIndexedByIndustry[$industry];

            $labels[$year][] = $row[1] . " (" . $row[3] . ")";
            $datasets[$year][] = $row[2];
        }
    }

    // -------

    $currentABNsDataset = array();
    $currentABNsLabels = array();

    $abnsRaw = file_get_contents("ABNs.csv");

    $rows = str_getcsv($abnsRaw, PHP_EOL);
    $isFirst = true;
    foreach ($rows as $row) {
        if (!$isFirst) {
            $rowWithFields = str_getcsv($row, ",");

            $currentABNsLabels[] = $rowWithFields[1] . " (" . $rowWithFields[3] . ")";
            $currentABNsDataset[] = $rowWithFields[2];
        }
        $isFirst = false;
    }
?>

<!doctype html>
<html>

<head>
	<title>Pie Chart</title>
    <script
      src="https://code.jquery.com/jquery-3.4.1.min.js"
      integrity="sha256-CSXorXvZcTkaix6Yvo6HppcZGetbYMGWSFlBw8HfCJo="
      crossorigin="anonymous"></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.8.0/Chart.bundle.min.js"></script>

    <style type="text/css">
        body {
            font-family: Helvetica, sans-serif;
        }

        div.chart-container {
            text-align: center;
            border: 1px solid black;
            margin-left: auto;
            margin-right: auto;
            margin-top: 20px;
        }

        #pie-controls {
            text-align: center;
            padding: 5px;
        }
        #pie-controls .year {
            font-size: 18px;
            text-decoration: underline;
            display: inline-block;
            margin-left: 5px;
            margin-right: 5px;
            padding: 10px;
            cursor: pointer;
        }
        #pie-controls .year.selected {
            font-weight: bold;
            text-decoration: none;
            background-color: grey;
            color: white;
        }

        canvas {
			-moz-user-select: none;
			-webkit-user-select: none;
			-ms-user-select: none;
		}
    </style>
</head>

<body>
	<div class="chart-container" id="pie-canvas-holder" style="width:900px; height: 500px;">
		<canvas id="pie-chart-area"></canvas>
	</div>
    <div id="pie-controls"></div>
	<script>
		var data = Array();
        var labels = Array();

        <?php
            foreach ($labels as $year => $rows) {
                echo "labels[$year] = " . json_encode($rows) . PHP_EOL;
            }
            foreach ($datasets as $year => $rows) {
                echo "data[$year] = " . json_encode($rows) . PHP_EOL;
            }
        ?>

		var pieConfig = {
			type: 'pie',
			data: {
				datasets: [{
					data: data[1896],
					backgroundColor: [
						"#488f31",
						"#6ca257",
						"#8eb67c",
						"#afc9a2",
						"#d0ddc9",
						"#f1f1f1",
						"#f1cfce",
						"#eeadad",
						"#e88b8d",
						"#df676e",
						"#de425b",
                        "#488f31",
						"#6ca257",
						"#8eb67c",
						"#afc9a2",
						"#d0ddc9",
						"#f1f1f1",
						"#f1cfce",
						"#eeadad",
						"#e88b8d",
						"#df676e",
						"#de425b",
					],
					label: 'Percentage of businesses by year'
				}],
				labels: labels[1896]
			},
			options: {
				responsive: true,
                cutoutPercentage: 40,
                legend: {
                    position: "right"
                },
                animation: {
                    animateRotate: true,
                    animateScale: true,
                }
			}
		};

        <?php foreach ($years as $year) { ?>
            $("#pie-controls").append(
                $("<span />")
                    .attr("class", "year")
                    .text(<?php echo $year; ?>)
                    .click(function() {
                        var year = <?php echo $year; ?>;
            			pieConfig.data.datasets[0].data = data[year];
            			pieConfig.data.labels = labels[year];
            			window.pie.update();

                        $("#pie-controls .year").removeClass("selected");
                        $(this).addClass("selected");
                    })
            );
        <?php } ?>
        $("#pie-controls .year").first().addClass("selected");
	</script>

    <div class="chart-container" id="abns-pie-canvas-holder" style="width:900px; height: 500px;">
		<canvas id="abns-pie-chart-area"></canvas>
	</div>
	<script>
		var abnsPieConfig = {
			type: 'pie',
			data: {
				datasets: [{
					data: <?php echo json_encode($currentABNsDataset); ?>,
					backgroundColor: [
						"#488f31",
						"#6ca257",
						"#8eb67c",
						"#afc9a2",
						"#d0ddc9",
						"#f1f1f1",
						"#f1cfce",
						"#eeadad",
						"#e88b8d",
						"#df676e",
						"#de425b",
                        "#488f31",
						"#6ca257",
						"#8eb67c",
						"#afc9a2",
						"#d0ddc9",
						"#f1f1f1",
						"#f1cfce",
						"#eeadad",
						"#e88b8d",
						"#df676e",
						"#de425b",
						"#488f31",
						"#6ca257",
						"#8eb67c",
						"#afc9a2",
						"#d0ddc9",
						"#f1f1f1",
						"#f1cfce",
						"#eeadad",
						"#e88b8d",
						"#df676e",
						"#de425b",
					],
					label: 'Percentage of businesses in 2016'
				}],
				labels: <?php echo json_encode($currentABNsLabels); ?>
			},
			options: {
				responsive: true,
                cutoutPercentage: 40,
                legend: {
                    position: "right"
                },
                animation: {
                    animateRotate: true,
                    animateScale: true,
                }
			}
		};

		window.onload = function() {
			var ctx = document.getElementById('pie-chart-area').getContext('2d');
			window.pie = new Chart(ctx, pieConfig);

			var ctx = document.getElementById('abns-pie-chart-area').getContext('2d');
			window.abnsPie = new Chart(ctx, abnsPieConfig);
		};
	</script>
</body>

</html>
