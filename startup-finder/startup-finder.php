<?php
/**
 * @package Startup Finder
 * @version 0.1
 */
/*
Plugin Name: Startup Finder
Plugin URI: http://virexmachina.com/
Description: Proof of Concept startup finder for CrunchBase data
Author: Adrian Gonzales
Version: 0.1
*/


/**
 * Shortcode for displaying results
 */
function vem_display_startup_results_func( $atts ){
	// Manage shortcode attributes
	extract( shortcode_atts( array(
		'geo' => 94301,
		'range' => 10,
		'pages' => 1,
	), $atts ) );

	// Initialize CrunchBase
	require "lib/crunchbase.php";
	$cb = new CrunchBase("dmyjysag5rt8sd75s89ckwq4"); // Init with API key

	// Setup Search
	$search_params = array(
		"geo" => $geo,
		"range" => $range,
	);

	// Do Search, get 5 pages
	$company_results = $cb->search($search_params, $pages);
	$companies = array();

	foreach($company_results as $co){
		// Get details on company
		$details = $cb->entity("company", $co->permalink);

		// Filter bad results, sometimes Crunchbase 404's their results?
		if(!property_exists($details, 'error')){
			// Filter by requirements
			if(
				($details->founded_year != "" && $details->founded_year < (int)date("Y")) && // Founded at least a year ago
				($details->number_of_employees > 0 && $details->number_of_employees <= 500)	// Less than 500 employees
			){

				// Calculate growth index
				// Number of Employees divided by years in biz
				$details->growth_index = $details->number_of_employees / ((int)date("Y") - $details->founded_year);

				$companies[] = $details;
			}
		}
	}

	// Sort by growth_index
	$growth_array = Array(); 
	foreach($companies as &$co){
		$growth_array[] = &$co->growth_index;
	}
	array_multisort($growth_array, SORT_DESC, $companies);


	// Crappy string concatation due to time constraints
	// I'd rather use a template lib for output flexibility
	$html = "<table><thead><tr><th>&nbsp;</th><th>Company Name</th><th>Employees</th><th>Year Founded</th><th>Growth Index</th></tr></thead><tbody>";
	$count = 1;
	foreach($companies as $c){
		$html .= "<tr>";
		$html .= "<td>$count</td>";
		$html .= "<td><a href=\"$c->crunchbase_url\" target=\"_blank\">$c->name</a></td>";
		$html .= "<td>".$c->number_of_employees."&nbsp;</td>";
		$html .= "<td>".$c->founded_year."&nbsp;</td>";
		$html .= "<td>".$c->growth_index."&nbsp;</td>";
		$html .= "</tr>";
		$count++;
	};
	$html .= "</tbody></table>";

	return $html;
}

// Setup shortcode hook for WP
add_shortcode( 'startupfinder', 'vem_display_startup_results_func' );