'use strict';

class lineChart {

	constructor(arrDatas, elementId, options) {
		
		if (elementId !== undefined)
		{
			this.options = {
				'margin' : {},
				'width': null,
				'height': null,
				'format': d3.format("1"),
				'color': null,
				'redrawDuration': 1000,
				'limits': null
			};
			this.axis = {
				'x' : {
					'axis': null,
					'pos': null,
					'domain': [1, 10],
					'ticks': 10,
					'centered': false,
					'type': 'text'
				},
				'y' : {
					'axis': null,
					'pos': null,
					'domain': [1, 10],
					'ticks': 10
				}
			};

			this.colors = new Array(
				'#1976D2',
				'#388E3C',
				'#5D4037',
				'#0097A7'
			);
			
			if (typeof options.width !== 'number')
			{
				if (options.width.indexOf('%') !== -1)
					options.width = document.querySelector("#" + elementId).offsetWidth;
			}

			this.options.margin = (options.margin !== undefined ? options.margin : {top: 20, right: 20, bottom: 20, left: 30 });
			this.options.width = (options.width !== undefined ? (options.width - this.options.margin.left - this.options.margin.right) : (850 - this.options.margin.left - this.options.margin.right));
			this.options.height = (options.height !== undefined ? (options.height - this.options.margin.top - this.options.margin.bottom) : (400 - this.options.margin.top - this.options.margin.bottom));
			
			//Axis configuration
			(options.format !== undefined ? this.options.format = options.format : this.options.format = d3.format("1"));
			(options.xAxis !== undefined ? this.axis.x = options.xAxis : undefined);
			(options.yAxis !== undefined ? this.axis.y = options.yAxis : undefined);
			(options.color !== undefined ? this.options.color = options.color : this.options.color = '#558B2F');
			(options.redrawDuration !== undefined ? this.options.redrawDuration = options.redrawDuration : undefined);
			(options.limits !== undefined ? this.options.limits = options.limits : undefined);
			
			this.lineDrawer = d3.line()
		        .curve((this.axis.x.type === 'date' ? d3.curveBasis : d3.curveMonotoneX))
		        .x((d, idx) => { 
		        	return this.axis.x.axis( (this.axis.x.type === 'date' ? new Date(d.name) : idx + 1))
		        })
		        .y((d) => { 
		        	return this.axis.y.axis((typeof d.val === "number" ? d.val : d.val.replace(',', '.')));
		        });

			this.svgElem = d3.select("#" + elementId).append("svg")
		        .attr('id', Math.floor((1 + Math.random()) * 0x10000).toString(16).substring(1))
		        .attr("width", this.options.width + this.options.margin.left + this.options.margin.right)
		        .attr("height", this.options.height + this.options.margin.top + this.options.margin.bottom);

		    this.container = this.svgElem.append("g")
		        .attr("transform", "translate(" + this.options.margin.left + "," + this.options.margin.top + ")");

		    

		    /*if ((options.xAxis.domain[1] + 1) === arrDatas.length)
			{
				this.line = arrDatas;
			}
			else
			{*/
				this.line = arrDatas;
			//}

			this.drawAxis();
		}


	}

	drawAxis(onlyAxis = false) {
		
	    if (this.axis.x.type === 'date')
	    {
	    	this.axis.x.axis = d3.scaleTime()
		        .domain(this.axis.x.domain)
		        .range([0, this.options.width]);
	    }
	    else
	    {
	    	this.axis.x.axis = d3.scaleLinear()
		        .domain(this.axis.x.domain)
		        .range([0, this.options.width]);	
	    }

	    if (this.axis.x.centered === true)
	    {	
		    this.axis.x.pos = d3.axisBottom(this.axis.x.axis)
		    	.ticks(0)
		    	.tickSizeOuter(0)
		        .tickSizeInner(0)
		        .tickFormat(this.options.format, function(d){return d.num});

		    this.container.append("g")
		        .attr("class", "x axis")
		        .attr('transform', 'translate(0, ' + (this.options.height / 2) + ')')
		        .call(this.axis.x.pos);
		}
		else
		{
			
			this.axis.x.pos = d3.axisBottom(this.axis.x.axis)
		    	.ticks(20)
		    	.tickSizeOuter(5)
		        .tickSizeInner(-(this.options.height));

		    this.container.append("g")
		        .attr("class", "x axis")
		        .attr('transform', 'translate(0, ' + (this.options.height) + ')')
		        .call(this.axis.x.pos)
		        .selectAll("text")
		            .style("text-anchor", "end")
		            .attr("dx", "0.2em")
		            .attr("dy", "1.2em");
		}

	    this.axis.y.axis = d3.scaleLinear()
	        .domain(this.axis.y.domain)
	        .range([this.options.height, 0]);

	    this.axis.y.pos = d3.axisLeft(this.axis.y.axis)
	    	.ticks(this.axis.y.ticks)
	        .tickSizeInner(-(this.options.width))
	        .tickSizeOuter(50)
	        .tickPadding(10);

		if (this.options.limits !== null )
			this.drawLimits();

		this.drawLines();

	    this.container.append("g")
	        .attr("class", "y axis")
	        .attr('fill', 'white')
	        .call(this.axis.y.pos);
	}

	drawLines() {
		// Append marker
		this.bisect = d3.bisector(function(num) {return num.name;}).right;
		
		if (this.line !== undefined)
		{
			this.container
				.append('svg:path')
		        .attr('d', this.lineDrawer(this.line))
		        .attr('class', 'lineBase')
		        .attr("id", 'nominalLine')
		        .attr('stroke', (this.options.color === '' ? this.getRandomColor() : this.options.color))
		        .attr('stroke-width', 1.5)
		        .attr('fill', 'none');
		}
		else
		{
			for(let idxLine in this.lines)
			{
				this.container
					.append('path')
			        .attr('d', this.lineDrawer(this.lines[idxLine]))
			        .attr('class', 'lineBase')
			        .attr("id", 'nominalLine'+idxLine)
			        .attr('stroke', (this.options.color === '' ? this.getRandomColor() : this.options.color))
			        .attr('stroke-width', 1.5)
			        .attr('fill', 'none');
			}	
		}
	}

	drawLimits() {
		for(let elm in this.options.limits) {
			if (typeof this.options.limits[elm] !== 'number')
			{
				this.container
					.append('line')
			        .attr('x1', 0)
			        .attr('y1', this.axis.y.axis(this.options.limits[elm].match(/,/) === null ? this.options.limits[elm] : parseFloat(this.options.limits[elm])))
			        .attr('x2', this.axis.x.axis(this.axis.x.domain[1]))
			        .attr('y2', this.axis.y.axis(this.options.limits[elm].match(/,/) === null ? this.options.limits[elm] : parseFloat(this.options.limits[elm])))
			        .attr('class', 'lineLimits')
			        .attr("id", 'limLine')
			        .attr('stroke', this.options.color)
			        .attr('stroke-width', 2)
			        .attr('fill', 'none');
		    }
		    else
		    {
		    	this.container
					.append('line')
			        .attr('x1', 0)
			        .attr('y1', this.axis.y.axis(this.options.limits[elm]))
			        .attr('x2', this.axis.x.axis(this.axis.x.domain[1]))
			        .attr('y2', this.axis.y.axis(this.options.limits[elm]))
			        .attr('class', 'lineLimits')
			        .attr("id", 'limLine')
			        .attr('stroke', this.options.color)
			        .attr('stroke-width', 2)
			        .attr('fill', 'none');
		    }
		};
		
	}

	redraw(domain = [], ticks = undefined) {
		if (domain.length === 2)
		{
			this.axis.y.domain = domain;
			this.axis.y.ticks = ticks;
			this.axis.y.axis = d3.scaleLinear()
		        .domain(this.axis.y.domain)
		        .range([this.options.height, 0]);

			this.axis.y.pos = d3.axisLeft(this.axis.y.axis)
		    	.ticks(this.axis.y.ticks)
		        .tickSizeInner(-(this.options.width))
		        .tickSizeOuter(50)
		        .tickPadding(10);
			this.container.select(".y.axis").transition(d3.transition().duration(500).delay(15).ease(d3.easeLinear)).call(this.axis.y.pos);
		}
		if (this.line !== undefined)
		{
			this.container.select('path#nominalLine')
				.data([this.line])
				.attr("d", this.lineDrawer)
				.attr("transform", "translate(" + this.axis.x.axis(1) + ")")
				.transition(d3.transition().duration(this.options.redrawDuration).delay(15).ease(d3.easeLinear))
				.on('end', () => {
					//Todo on ended
				})
				.attr("transform", "translate(" + this.axis.x.axis(0) + ")");
		}
		else
		{
			for(let idxLine in this.lines)
			{
				this.container.select('path#nominalLine'+idxLine)
					.data([this.lines[idxLine]])
					.attr("d", this.lineDrawer)
					.attr("transform", "translate(" + this.axis.x.axis(1) + ")")
					.transition(d3.transition().duration(this.options.redrawDuration).delay(15).ease(d3.easeLinear))
					.on('end', () => {
						//Todo on ended
					})
					.attr("transform", "translate(" + this.axis.x.axis(0) + ")");
			}
		}
	}

	getRandomColor() {
		let rand = Math.round(Math.random() * ((this.colors.length - 1) - 0 ) + 0);
		
		let color = this.colors[rand];

		this.colors.splice(rand, 1);
		
		return color;
	}
}