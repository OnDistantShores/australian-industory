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

    $tmLinkData = array();

    $tmlinkRaw = file_get_contents("tmlink.csv");

    // Index the data by year
    $rows = str_getcsv($tmlinkRaw, PHP_EOL);
    $isFirst = true;
    foreach ($rows as $row) {
        if (!$isFirst) {
            $rowWithFields = str_getcsv($row, ",");
            $decadeName = $rowWithFields[0] . "s";

            if (!isset($tmLinkData[$decadeName])) {
                $tmLinkData[$decadeName] = array();
            }
            $tmLinkData[$decadeName][] = $rowWithFields;
        }
        $isFirst = false;
    }

    $tmLinkDecades = array_keys($tmLinkData);

    $tmLinkLabels = array();
    $tmLinkDatasets = array();

    $setIndustryOrder = null;

    foreach ($tmLinkData as $decade => $rows) {
        $tmLinkLabels[$decade] = array();
        $tmLinkDatasets[$decade] = array();

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

            $tmLinkLabels[$decade][] = $row[1] . " (" . $row[3] . ")";
            $tmLinkDatasets[$decade][] = $row[2];
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
            echo "Error: no matching entry for $industry<br />";
            continue;
        }

        $abrVsNeisNeisDataset[] = str_replace("%", "", str_replace("<", "", $matchingEntry[4])); // Just take the raw % number, not the other characters

    }
?>

<!DOCTYPE html>
<html lang="en">
  <head>
	<title>Australian InduStory :: GovHack 2019</title>
    <script
      src="https://code.jquery.com/jquery-3.4.1.min.js"
      integrity="sha256-CSXorXvZcTkaix6Yvo6HppcZGetbYMGWSFlBw8HfCJo="
      crossorigin="anonymous"></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.8.0/Chart.bundle.min.js"></script>

    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <link href="https://fonts.googleapis.com/css?family=Poppins:100,200,300,400,500,600,700,800,900" rel="stylesheet">

    <link rel="stylesheet" href="css/open-iconic-bootstrap.min.css">
    <link rel="stylesheet" href="css/animate.css">

    <link rel="stylesheet" href="css/owl.carousel.min.css">
    <link rel="stylesheet" href="css/owl.theme.default.min.css">
    <link rel="stylesheet" href="css/magnific-popup.css">

    <link rel="stylesheet" href="css/aos.css">

    <link rel="stylesheet" href="css/ionicons.min.css">

    <link rel="stylesheet" href="css/flaticon.css">
    <link rel="stylesheet" href="css/icomoon.css">
    <link rel="stylesheet" href="css/style.css">

    <style type="text/css">
        div.chart-container {
            text-align: center;
            border: 1px solid black;
            margin-left: auto;
            margin-right: auto;
            margin-top: 20px;
            margin-bottom: 20px;
            padding-top: 10px;
        }

        .pie-controls {
            text-align: center;
            padding: 5px;
            margin-bottom: 15px;
            margin-top: -10px;
        }
        .pie-controls .year {
            font-size: 18px;
            text-decoration: underline;
            display: inline-block;
            margin-left: 5px;
            margin-right: 5px;
            padding: 10px;
            cursor: pointer;
        }
        .pie-controls .year.selected {
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

        h1 span.title-story {
            color: #207dff;
        }
        nav {
            top: 0 !important;
        }
        .blockquote {
            font-style: italic;
        }
        div.heading-footnote {
            font-size: 14px;
            margin-top: -15px;
            text-align: right;
        }


        @media (min-width: 992px) {
            .p-lg-5 {
                padding-top: 0px;
                padding-bottom: 0px;
            }
        }
        .ftco-section {
            padding: 2em 0;
        }

    </style>
  </head>
  <body data-spy="scroll" data-target=".site-navbar-target" data-offset="300">

      <nav class="navbar navbar-expand-lg navbar-dark ftco_navbar bg-dark ftco-navbar-light site-navbar-target" id="ftco-navbar">
  	    <div class="container">
  	      <a class="navbar-brand" href="/">Australian InduStory</a>
  	      <button class="navbar-toggler js-fh5co-nav-toggle fh5co-nav-toggle" type="button" data-toggle="collapse" data-target="#ftco-nav" aria-controls="ftco-nav" aria-expanded="false" aria-label="Toggle navigation">
  	        <span class="oi oi-menu"></span> Menu
  	      </button>

  	      <div class="collapse navbar-collapse" id="ftco-nav">
  	        <ul class="navbar-nav nav ml-auto">
  	          <li class="nav-item"><a href="#home" class="nav-link"><span>Home</span></a></li>
  	          <li class="nav-item"><a href="#overview" class="nav-link"><span>Overview</span></a></li>
  	          <li class="nav-item"><a href="#the-story" class="nav-link"><span>The story</span></a></li>
  	          <li class="nav-item"><a href="#data-sources" class="nav-link"><span>Data sources</span></a></li>
  	          <li class="nav-item"><a href="#data-details" class="nav-link"><span>Data details</span></a></li>
  	          <li class="nav-item"><a href="#more-links" class="nav-link"><span>More links</span></a></li>
  	        </ul>
  	      </div>
  	    </div>
  	  </nav>

	  <section class="hero-wrap js-fullheight" style="background-image: url('images/industry-2-bw.jpg');" data-section="home" data-stellar-background-ratio="0.5">
      <div class="overlay"></div>
      <div class="container">
        <div class="row no-gutters slider-text js-fullheight align-items-center justify-content-start" data-scrollax-parent="true">
          <div class="col-md-6 pt-5 ftco-animate">
          	<div class="mt-5">
              		<h1 class="mb-4" style="color: white;">Australian<br />Indu<span class='title-story'>Story</span></h1>
	            <p class="mb-4" style="color: white;">"Where we've come from, where we are now,<br />and where we need help"</p>
	            <p><a href="#overview" class="btn btn-primary py-3 px-4">Get started</a></p>
            </div>
          </div>
        </div>
      </div>
    </section>

    <section class="ftco-counter img ftco-section ftco-no-pt ftco-no-pb" id="overview">
    <div class="container">
        <div class="row d-flex">
            <div class="col-md-6 col-lg-5 d-flex">
                <div class="img d-flex align-self-stretch align-items-center" style="background-image:url(images/industry-4.jpg);">
                </div>
            </div>
            <div class="col-md-6 col-lg-7 pl-lg-5 py-md-5">
                <div class="py-md-5">
                    <div class="row justify-content-start pb-3">
                  <div class="col-md-12 heading-section ftco-animate p-4 p-lg-5">
                    <h2 class="mb-4">Overview</h2>
                    <p>This is a project entry for GovHack 2019. I have attempted to tell a cohesive story about the past & present
                        of Australian industry by homogenising, contextualising & visualising a number of disparate datasets, namely the
                        <a href='https://drive.google.com/drive/folders/1D0Yj4-Lr-P2XEzuv4L2M_sEBhkCGUtem' target='_blank'>Sands &
                        McDougal business directory data</a>,
                        <a href='https://data.gov.au/data/dataset/tm-link' target='_blank'>TM-Link trademark records data</a>,
                        <a href='https://data.gov.au/dataset/ds-dga-8fc2e133-29f4-4123-a977-b245b32e62df/details?q=' target='_blank'>ABN register data</a> and
                        <a href='https://data.gov.au/dataset/ds-dga-932648b1-7ca1-46c4-99ba-d9a41f98d42f/details?q=NEIS' target='_blank'>
                            New business assistance with NEIS data</a>. I hope you like it!</p>
                  </div>
                </div>
            </div>
        </div>
    </div>
    </div>
    </section>

    <section class="ftco-section" id="the-story">
        <div class="container-fluid px-5">
            <div class="row justify-content-center mb-5 pb-2">
                <div class="col-md-8 text-left heading-section ftco-animate">
                    <h2 class="mb-4">The story</h2>

                    <blockquote class="blockquote ftco-animate">
                        <p class="mb-0">"You have to know the past to understand the present" - Carl Sagan</p>
                    </blockquote>

                    <p>Australia's growth from settlement to the "lucky" country we enjoy today has been in no small part due
                        to an ever-changing story of economic growth.</p>

                    <p>In the early days of European settlement, industrial and commercial development was slow. This began to pick
                        up after 1820 when the pastoral industry started to grow, leading to Australia supplying over half of the
                        British market's wool by 1850. This led to growth in manufacturing, and the the gold rush of the 1850s
                        encouraged a rapid expansion of the financial industry. Manufacturing continued to grow in response to
                        increased government efforts in community development towards the end of the 19th century.</p>

                    <p>The <a href='https://drive.google.com/drive/folders/1D0Yj4-Lr-P2XEzuv4L2M_sEBhkCGUtem' target='_blank'>Sands &
                        McDougal business directory data</a> is a fascinating resource of the complexion of Victorian businesses
                        from this time period onward. Its first records appear in 1896, when manufacturing was still the biggest
                        contributor to our country's wealth.</p>

                    <h2 class="mb-4 ftco-animate">Industry mix of businesses over time</h2>
                    <div class="heading-footnote ftco-animate">(Victoria, 1896-1975)</div>

                	<div class="chart-container ftco-animate" id="pie-canvas-holder" style="width:900px; height: 480px;">
                		<canvas id="pie-chart-area"></canvas>
                	</div>
                    <div class="pie-controls" id="pie-controls"></div>
                	<script>
                        var colours = [
                            "#003f5c",
                            "#2f4b7c",
                            "#665191",
                            "#a05195",
                            "#d45087",
                            "#f95d6a",
                            "#ff7c43",
                            "#ffa600",
                        ];

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
                					backgroundColor: colours.concat(colours).concat(colours),
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

                        var yearButtons = Array();
                        <?php foreach ($years as $year) { ?>
                            var newYearButton = $("<span />")
                                .attr("class", "year")
                                .text(<?php echo $year; ?>)
                                .click(function(event) {
                                    handleYearClick(event.target, true);
                                });
                            $("#pie-controls").append(newYearButton);
                            yearButtons.push(newYearButton);
                        <?php } ?>
                        $("#pie-controls .year").first().addClass("selected");

                        var handleYearClick = function(button, wasOrganicClick) {
                            var year = $(button).text();
                            pieConfig.data.datasets[0].data = data[year];
                            pieConfig.data.labels = labels[year];
                            window.pie.update();

                            $("#pie-controls .year").removeClass("selected");
                            $(button).addClass("selected");

                            // Reset the timer - different amount of time depending on the type of click
                            clearTimeout(timerHandle);
                            timerHandle = setTimeout(autoMoveYear, (wasOrganicClick ? 20000 : 2000));
                        };

                        var autoMoveYear = function() {
                            var selectNextItem = false;
                            $.each(yearButtons, function(index, element) {
                                if (selectNextItem) {
                                    handleYearClick(element, false);
                                    selectNextItem = false;
                                }
                                else if ($(element).hasClass("selected")) {
                                    selectNextItem = true;
                                }
                            });
                            if (selectNextItem) { // Ths would happen if the last element was previously selected
                                handleYearClick($(yearButtons[0]), false);
                            }
                        };
                        var timerHandle = setTimeout(autoMoveYear, 2000);
                	</script>

                    <p>This incredible resource brings to life the story of our nation over the 80-odd year period it captures.
                        Manufacturing continued to grow strongly in the early 20th century, as it became a huge supplier during WW2.
                        Although it was quickly surpassed proportionally by a booming retail industry driven by our strong economy.
                        We can see how services industries experienced steady growth across this time, but especially into the
                        1970s when oil price rises led to recession and a slowing of traditional industries.</p>

                    <p>The trend of manufacturing's decline and the service industry's rise continued into the 80s and 90s.
                        During this period, where the <b>Sands & McDougal business directory data</b> coverage concludes, <thead>
                        <a href='https://data.gov.au/data/dataset/tm-link' target='_blank'>TM-Link trademark records data</a>
                        can help to give context of the type of devleopments industry was working on in this time. Up until the 60s,
                        this data had entirely focussed on consumer & industrial goods trademarks. But around the 70s, there began
                        a rise of trademarks relating to services according to the
                        <a href='https://en.wikipedia.org/wiki/International_(Nice)_Classification_of_Goods_and_Services' target='_blank'>'Nice' classification</a>.</p>

                    <h2 class="mb-4 ftco-animate">Industry mix of services trademarks over time</h2>
                    <div class="heading-footnote ftco-animate">(Australia, 1970-1999)</div>

                	<div class="chart-container ftco-animate" id="tmlink-pie-canvas-holder" style="width:900px; height: 480px;">
                		<canvas id="tmlink-pie-chart-area"></canvas>
                	</div>
                    <div class='pie-controls' id="tmlink-pie-controls"></div>
                	<script>
                        var colours = [
                            "#003f5c",
                            "#2f4b7c",
                            "#665191",
                            "#a05195",
                            "#d45087",
                            "#f95d6a",
                            "#ff7c43",
                            "#ffa600",
                        ];

                		var tmLinkData = Array();
                        var tmLinkLabels = Array();

                        <?php
                            foreach ($tmLinkLabels as $decade => $rows) {
                                echo "tmLinkLabels[\"$decade\"] = " . json_encode($rows) . PHP_EOL;
                            }
                            foreach ($tmLinkDatasets as $decade => $rows) {
                                echo "tmLinkData[\"$decade\"] = " . json_encode($rows) . PHP_EOL;
                            }
                        ?>

                		var tmLinkPieConfig = {
                			type: 'pie',
                			data: {
                				datasets: [{
                					data: tmLinkData["1970s"],
                					backgroundColor: colours,
                					label: 'Percentage of classifiable trademarks per year'
                				}],
                				labels: tmLinkLabels["1970s"]
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

                        var tmLinkDecadeButtons = Array();
                        <?php foreach ($tmLinkDecades as $decade) { ?>
                            var newDecadeButton = $("<span />")
                                .attr("class", "year")
                                .text("<?php echo $decade; ?>")
                                .click(function(event) {
                                    handleTmLinkDecadeClick(event.target, true);
                                });
                            $("#tmlink-pie-controls").append(newDecadeButton);
                            tmLinkDecadeButtons.push(newDecadeButton);
                        <?php } ?>
                        $("#tmlink-pie-controls .year").first().addClass("selected");

                        var handleTmLinkDecadeClick = function(button, wasOrganicClick) {
                            var decade = $(button).text();

                            tmLinkPieConfig.data.datasets[0].data = tmLinkData[decade];
                            tmLinkPieConfig.data.labels = tmLinkLabels[decade];
                            window.tmLinkPie.update();

                            $("#tmlink-pie-controls .year").removeClass("selected");
                            $(button).addClass("selected");

                            // Reset the timer - different amount of time depending on the type of click
                            clearTimeout(tmLinkTimerHandle);
                            tmLinkTimerHandle = setTimeout(tmLinkAutoMoveDecade, (wasOrganicClick ? 20000 : 2000));
                        };

                        var tmLinkAutoMoveDecade = function() {
                            var selectNextItem = false;
                            $.each(tmLinkDecadeButtons, function(index, element) {
                                if (selectNextItem) {
                                    handleTmLinkDecadeClick(element, false);
                                    selectNextItem = false;
                                }
                                else if ($(element).hasClass("selected")) {
                                    selectNextItem = true;
                                }
                            });
                            if (selectNextItem) { // Ths would happen if the last element was previously selected
                                handleTmLinkDecadeClick($(tmLinkDecadeButtons[0]), false);
                            }
                        };
                        var tmLinkTimerHandle = setTimeout(tmLinkAutoMoveDecade, 2000);
                	</script>

                    <p>This data shows a decline in financial, construction & transport services developments over this time.
                        Meanwhile, the education and media industries saw significant leaps forward, as Australia
                        embraced the growth of the information age. Administrative & support services also grew
                        substantially during this period.</p>

                    <p>This brings us up to where we are now.</p>

                    <h2 class="mb-4 ftco-animate">Current industry mix according to ABN register</h2>
                    <div class="heading-footnote ftco-animate">(Australia, 2016)</div>

                    <div class="chart-container ftco-animate" id="abns-pie-canvas-holder" style="width:900px; height: 480px;">
                		<canvas id="abns-pie-chart-area"></canvas>
                	</div>
                	<script>
                		var abnsPieConfig = {
                			type: 'pie',
                			data: {
                				datasets: [{
                					data: <?php echo json_encode($currentABNsDataset); ?>,
                					backgroundColor: colours.concat(colours),
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

                    <p>Today, Australia is dominated by its services sector, making up almost 80% of our workforce. This is evident in
                        the <a href='https://data.gov.au/dataset/ds-dga-8fc2e133-29f4-4123-a977-b245b32e62df/details?q=' target='_blank'>ABN register data</a>,
                        showing areas like finance, professional services & real estate as very strong. Some of these areas show significant
                        growth since the end of the business directory dataset in 1975.
                        While manufacturing is still a strong industry, its strength is consolidated in a relatively smaller
                        number of larger companies, hence being a smaller proportion on the ABN register and a major decrease
                        since the end of the business directory dataset. Retail has similarly fallen dramatically.</p>

                    <p>The Australian government <a href='https://joboutlook.gov.au/futureofwork' target='_blank'>predicts</a> that the
                        biggest jobs growth in the next 5 years will be in the areas of <strong>Health care & social assistance</strong>,
                        <strong>Construction</strong>, <strong>Education & training</strong> and <strong>Professional, scientific &
                        technical services</strong>. How well-placed are we looking for growth in these areas?</p>

                    <p>The <a href='https://data.gov.au/dataset/ds-dga-932648b1-7ca1-46c4-99ba-d9a41f98d42f/details?q=NEIS' target='_blank'>
                        New business assistance with NEIS</a> data shows us how many emerging businesses we're seeing
                        in these industries. This gives us indicators about how well-placed we'll be in future years to meet economic
                        demands on our different markets, and how mature we can expect the workforce to be in these areas.</p>

                    <h2 class="mb-4 ftco-animate">Industries of today's businesses according to ABN register v.s. NEIS enrollments</h2>
                    <div class="heading-footnote ftco-animate">(compared proportionally, excluding "Other")</div>

                	<div class="chart-container ftco-animate" id="bar-container" style="width:900px; height: 480px;">
                		<canvas id="bar-canvas"></canvas>
                	</div>

                    <script>
                		var horizontalBarChartData = {
                			labels: <?php echo json_encode($abrVsNeisLabels); ?>,
                			datasets: [{
                				label: 'ABN register',
                				backgroundColor: "#003f5c",
                				data: <?php echo json_encode($abrVsNeisAbrDataset); ?>
                			}, {
                				label: 'NEIS data',
                				backgroundColor: "#ffa600",
                				data: <?php echo json_encode($abrVsNeisNeisDataset); ?>
                			}]
                		};

                		window.onload = function() {
                			var pieCtx = document.getElementById('pie-chart-area').getContext('2d');
                			window.pie = new Chart(pieCtx, pieConfig);

                			var tmLinkCtx = document.getElementById('tmlink-pie-chart-area').getContext('2d');
                			window.tmLinkPie = new Chart(tmLinkCtx, tmLinkPieConfig);

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
                                    },
                                    tooltips: {
                                        callbacks: {
                                            label: function(tooltipItem, data) {
                                                return tooltipItem.value + "%";
                                            }
                                        }
                                    }
                				}
                			});
                		};
                	</script>

                    <p>This data shows a significant gap between the current industrial mix of businesses and those on the rise - and
                        that's not necessarily a bad thing. We see strong interest from new businesses in growth areas such as professional,
                        scientific & technical services, as well as in stalwarts such as manufacturing. It's reassuring an appropriate focus
                        on the other growth areas of health care & social assistance as well as education & training. However there does
                        appear to be hints of a significant shortfall in constructure. It is a surprise to see dramatically few businesses
                        launching into the financial insurance space considering the current strength of that industry.</p>

                    <p>Thank you for your attention and interest in this proejct. I hope you've found it both informative & interesting!</p>

                </div>
            </div>
        </div>
    </section>

    <section class="ftco-counter img ftco-section ftco-no-pt ftco-no-pb" id="data-sources">
    <div class="container">
        <div class="row d-flex">
            <div class="col-md-6 col-lg-5 d-flex">
                <div class="img d-flex align-self-stretch align-items-center" style="background-image:url(images/industry-3.jpg);">
                </div>
            </div>
            <div class="col-md-6 col-lg-7 pl-lg-5 py-md-5">
                <div class="py-md-5">
                    <div class="row justify-content-start pb-3">
                  <div class="col-md-12 heading-section ftco-animate p-4 p-lg-5">
                    <h2 class="mb-4">Data sources</h2>
                    <p><ul>
                        <li><a href='https://drive.google.com/drive/folders/1D0Yj4-Lr-P2XEzuv4L2M_sEBhkCGUtem' target='_blank'>Sands &
                                McDougal business directory data</a></li>
                        <li><a href='https://data.gov.au/data/dataset/tm-link' target='_blank'>TM-Link trademark records data</a></li>
                        <li><a href='https://data.gov.au/dataset/ds-dga-8fc2e133-29f4-4123-a977-b245b32e62df/details?q=' target='_blank'>ABN register data</a></li>
                        <li><a href='https://data.gov.au/dataset/ds-dga-932648b1-7ca1-46c4-99ba-d9a41f98d42f/details?q=NEIS' target='_blank'>
                            New business assistance with NEIS</a></li>
                    </ul></p>
                  </div>
                </div>
            </div>
        </div>
    </div>
    </div>
    </section>


    <section class="ftco-counter img ftco-section ftco-no-pt ftco-no-pb" id="data-details">
    <div class="container">
        <div class="row d-flex">
            <div class="col-md-6 col-lg-7 pl-lg-5 py-md-5">
                <div class="py-md-5">
                    <div class="row justify-content-start pb-3">
                  <div class="col-md-12 heading-section ftco-animate p-4 p-lg-5">
                    <h2 class="mb-4">Data details & notes</h2>
                    <p>Some various detailed notes about how this data was used:<ul>
                        <li>The business history only covers Victoria, whereas other data was Australia-wide. Unfortunately no
                            equivalent national data was available, and I used it under the assumption that the
                            Victorian data was regardless representative of Australian data when taken as percentages.</li>
                        <li>Extremely minor groupings (&lt;13 businesses) were removed from 2016 ABN register data. These were
                            "government admin & defence" (2), "personal & other services" (2) and "multi-role organisation" (12).
                            Also removed blanks (497).</li>
                        <li>The "current" ABN register data was as at end of FY2016, whereas NEIS data covered 2015-2019.</li>
                        <li>The NEIS data did some combining of industries which have low representation with "Other Services" to
                            form a single "Other" category. I have done the same for the ABN register data with mining, "electricity,
                            gas, water & waste" and "public administration & safety", all of which were &lt;1%. In the ABN register data,
                            I combined forestry & fishing into agirculture, as the NEIS data also did.</li>
                        <li>TM-Link 'Nice' classifications were transformed to Australia's standard industry codes by mapping the official
                            definitions across, manually. I was surprised to found a very clear, near 1:1 mappping.</p>
                    </ul></p>
                  </div>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-lg-5 d-flex">
            <div class="img d-flex align-self-stretch align-items-center" style="background-image:url(images/industry-6.jpg);">
            </div>
        </div>
    </div>
    </section>

    <section class="ftco-counter img ftco-section ftco-no-pt ftco-no-pb" id="more-info">
    <div class="container">
        <div class="row d-flex">
            <div class="col-md-6 col-lg-5 d-flex">
                <div class="img d-flex align-self-stretch align-items-center" style="background-image:url(images/industry-5.jpg);">
                </div>
            </div>
            <div class="col-md-6 col-lg-7 pl-lg-5 py-md-5">
                <div class="py-md-5">
                    <div class="row justify-content-start pb-3">
                  <div class="col-md-12 heading-section ftco-animate p-4 p-lg-5">
                    <h2 class="mb-4">More info</h2>
                    <p><ul>
                        <li><a href='https://github.com/OnDistantShores/australian-industory' target='_blank'>Project code on Github</a></li>
                        <li><a href='https://hackerspace.govhack.org/projects/australian_industory' target='_blank'>Project entry page on Hackerspace</a></li>
                        <li><a href='mailto:cameron.ross@gmail.com' target='_blank'>Contact me</a></li>
                        <li>Other references used:<ul>
                            <li><a href='https://www.abs.gov.au/ausstats/abs@.nsf/Lookup/by%20Subject/1301.0~2012~Main%20Features~Evolution%20of%20Australian%20Industry~239' target='_blank'>Evolution of Australian industry</a></li>
                            <li><a href='https://en.wikipedia.org/wiki/Economy_of_Australia' target='_blank'>Economy of Australia</a></li>
                            <li><a href='https://joboutlook.gov.au/futureofwork' target='_blank'>Future of work</a></li>
                        </ul></li>
                    </ul></p>
                  </div>
                </div>
            </div>
        </div>
    </div>
    </div>
    </section>


    <div id="ftco-loader" class="show fullscreen"><svg class="circular" width="48px" height="48px"><circle class="path-bg" cx="24" cy="24" r="22" fill="none" stroke-width="4" stroke="#eeeeee"/><circle class="path" cx="24" cy="24" r="22" fill="none" stroke-width="4" stroke-miterlimit="10" stroke="#F96D00"/></svg></div>

    <script src="js/jquery.min.js"></script>
    <script src="js/jquery-migrate-3.0.1.min.js"></script>
    <script src="js/popper.min.js"></script>
    <script src="js/bootstrap.min.js"></script>
    <script src="js/jquery.easing.1.3.js"></script>
    <script src="js/jquery.waypoints.min.js"></script>
    <script src="js/jquery.stellar.min.js"></script>
    <script src="js/owl.carousel.min.js"></script>
    <script src="js/jquery.magnific-popup.min.js"></script>
    <script src="js/aos.js"></script>
    <script src="js/jquery.animateNumber.min.js"></script>
    <script src="js/scrollax.min.js"></script>

    <script src="js/main.js"></script>

</body>

</html>
