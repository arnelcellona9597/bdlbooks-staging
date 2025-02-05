@page { 
	margin-top: <?php echo $this->page_margins['top']; ?>mm;
	margin-bottom: <?php echo $this->page_margins['bottom']; ?>mm;
	margin-left: <?php echo $this->page_margins['left']; ?>mm;
	margin-right: <?php echo $this->page_margins['right']; ?>mm;
	/* The @page size attribute is unsupported by many browsers, but at lease we can try! http://stackoverflow.com/q/138422/1446634*/
	size: <?php echo $this->paper_size; ?>;
} 

html, body {
	margin: 0;
	padding: 0;
	border: none;
	height: 100%;
	font-size: <?php echo $this->font_size; ?>;
	font-family: sans-serif;
}
body {
	height: 99%; /* prevents extra page from being printed sometimes */
}

table.address-labels {
	border-collapse: <?php echo $this->border_collapse; ?>;
	border-spacing: <?php echo $this->horizontal_pitch; ?>mm <?php echo $this->vertical_pitch; ?>mm;
	page-break-after: always; 
}

.label-wrapper {
	/* adjust these values to a value smaller tan the actual label height/width if you're having trouble fitting the labels on a page */
	max-height: <?php echo $this->label_height; ?>mm;
	max-width: <?php echo $this->label_width; ?>mm;
	overflow: hidden;
}

td.label {
	vertical-align: middle;
	padding: 0;
	/*border: 1px solid black;*/
	/* ^^^ can be used for testing/debugging*/
}

.address-block {
	width: <?php echo $this->block_width; ?>;
	margin: auto;
	text-align: left;
	clear:both;
}

.qr-code {
	float: left;
}

ul.order-items,
ul.sku-list {
	list-style: none;
	margin: 0;
	padding:0;
}

ul.order-items .wc-item-meta { margin: 4px 0; }
ul.order-items .wc-item-meta p { display: inline; }
ul.order-items .wc-item-meta li {
	margin: 0;
	margin-left: 5px;
}