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

    $abrVsNeisLabels = array();
    $abrVsNeisAbrDataset = array();

    $abnsRaw = file_get_contents("ABNs.csv");

    $rows = str_getcsv($abnsRaw, PHP_EOL);
    $isFirst = true;
    foreach ($rows as $row) {
        if (!$isFirst) {
            $rowWithFields = str_getcsv($row, ",");

            $currentABNsLabels[] = $rowWithFields[1] . " (" . $rowWithFields[3] . ")";
            $currentABNsDataset[] = $rowWithFields[2];

            if ($rowWithFields[1] != "Other") {
                $abrVsNeisLabels[] = $rowWithFields[1];
                $abrVsNeisAbrDataset[] = str_replace("%", "", str_replace("<", "", $rowWithFields[3])); // Just take the raw % number, not the other characters
            }
        }
        $isFirst = false;
    }

    // -------

    $abrVsNeisNeisDataset = array();

    $neisRaw = file_get_contents("NEIS.csv");

    $neisRows = array();
    $rows = str_getcsv($neisRaw, PHP_EOL);
    $isFirst = true;
    foreach ($rows as $row) {
        if (!$isFirst) {
            $rowWithFields = str_getcsv($row, ",");

            // Store the rows by industry
            $neisRows[$rowWithFields[1]] = $rowWithFields;
        }
        $isFirst = false;
    }

    // Add the dataset according to the order set by the ABR data
    foreach ($abrVsNeisLabels as $industry) {
        $matchingEntry = $neisRows[$industry];
        if (!$matchingEntry) {
            echo "matching issue for $industry<br />";
            continue;
        }

        $abrVsNeisNeisDataset[] = str_replace("%", "", str_replace("<", "", $matchingEntry[4])); // Just take the raw % number, not the other characters

    }
?>

<!doctype html>
<html>

<head>
	<title>Australian InduStory :: GovHack 2019</title>
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
    <h1>Australian Indu<span class="story">Story</span></h1>
    <h2>"Where we've come from, where we're at, and where we need help"</h2>
    <h3>A GovHack 2019 project by CamBamBaJam</h3>

    <div>Menu: Overview :: The story :: Data sources used :: Data details & disclaimers :: More info</div>

    <p>Carl Sagan once said "You have to know the past to understand the present".</p>

    <h3>Industry mix historically over time</h3>
    <h4>(Victoria 1896-1975)</h4>

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

    <h3>Current industry mix according to ABR</h3>
    <h4>(Australia, 2016)</h4>

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
	</script>

    <h3>Industries of ABR businesses v.s. NEIS enrollments</h3>
</h4>(compared proportionally, excluding "Other")</h4>

	<div class="chart-container" id="bar-container" style="width:900px; height: 500px;">
		<canvas id="bar-canvas"></canvas>
	</div>

    <script>
		var horizontalBarChartData = {
			labels: <?php echo json_encode($abrVsNeisLabels); ?>, // TODO needs the % taken off
			datasets: [{
				label: 'ABR',
				backgroundColor: "#488f31",
				data: <?php echo json_encode($abrVsNeisAbrDataset); ?> // TODO needs to be the % data
			}, {
				label: 'NEIS',
				backgroundColor: "#de425b",
				data: <?php echo json_encode($abrVsNeisNeisDataset); ?>  // TODO needs to actually be the NEIS data
			}]
		};

		/*document.getElementById('randomizeData').addEventListener('click', function() {
			var zero = Math.random() < 0.2 ? true : false;
			horizontalBarChartData.datasets.forEach(function(dataset) {
				dataset.data = dataset.data.map(function() {
					return zero ? 0.0 : randomScalingFactor();
				});

			});
			window.myHorizontalBar.update();
		});*/

		window.onload = function() {
			var pieCtx = document.getElementById('pie-chart-area').getContext('2d');
			window.pie = new Chart(pieCtx, pieConfig);

			var abnsCtx = document.getElementById('abns-pie-chart-area').getContext('2d');
			window.abnsPie = new Chart(abnsCtx, abnsPieConfig);

			var barCtx = document.getElementById('bar-canvas').getContext('2d');
			window.bar = new Chart(barCtx, {
				type: 'horizontalBar',
				data: horizontalBarChartData,
				options: {
					// Elements options apply to all of the options unless overridden in a dataset
					// In this case, we are setting the border of each horizontal bar to be 2px wide
					elements: {
						rectangle: {
							borderWidth: 2,
						}
					},
					responsive: true,
					legend: {
						position: 'top',
					},
                    scales: {
                        xAxes: [{
                            ticks: {
                                // Include a % sign in the ticks
                                callback: function(value, index, values) {
                                    return value + "%";
                                }
                            }
                        }]
                    }
				}
			});
		};
	</script>
</body>

</html>
