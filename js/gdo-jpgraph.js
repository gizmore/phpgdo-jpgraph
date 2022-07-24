"use strict";
window.GDO.JPGraph = {
	
	initGraphFor: function(form) {
		console.log('GDO.JPGraph.initGraphFor()', form);
		this.setDisabledStateFor(form);
	},
			
	setDisabledStateFor: function(form) {
		var sel = form.find('select');
		var start = form.find('input[name=start]');
		var end = form.find('input[name=end]');
		console.log('GDO.JPGraph.setDisabledStateFor()', form, sel, start, end);
		switch (sel.val()) {
		case 'custom':
		start.prop('disabled', false);
		end.prop('disabled', false);
		break;
		default:
		start.prop('disabled', true);
		end.prop('disabled', true);
		break;
		}
	},

	renderImageFor: function(form) {
		console.log('GDO.JPGraph.renderImageFor()', form);
		
		var img = form.find('img');
		var sel = form.find('select');
		var start = form.find('input[name=start]');
		var end = form.find('input[name=end]');
		
		this._renderImage(img, sel.val(), start.val(), end.val());
	},
	
	_renderImage: function(img, date, start, end) {
		console.log('GDO.JPGraph._renderImage()', img, date, start, end);
		
		var src = img.attr('src');
		var newDate = "date="+date;
		var newStart = "start="+start;
		var newEnd = "end="+end;
		src = src.replace(/date=[^&]*/, newDate);
		src = src.replace(/start=[^&]*/, newStart);
		src = src.replace(/end=[^&]*/, newEnd);
		src = src.replace(/&t=[0-9]+/, '') + '&t='+(new Date().getTime());
		
		// Invalid custom date abort
		if (date === 'custom') {
			if ( (!start) || (!end) ) {
				return;
			}
		}
		
		// Please select abort
		if (date === '0') {
			return;
		}
		
		console.log(img, src);

		img.attr('src', src);
	},
	
};

$(function(){

	$('.gdt-graph-select').each(function(){
		GDO.JPGraph.initGraphFor($(this));
	});

	$('.gdt-graph-select select').change(function(){
		var sel = $(this);
		var form = sel.closest('form');
		GDO.JPGraph.setDisabledStateFor(form);
		GDO.JPGraph.renderImageFor(form);
	});

	$('.gdt-graph-select input').change(function(){
		var input = $(this);
		var form = input.closest('form');
		GDO.JPGraph.renderImageFor(form);
	});

});
