$(function(){
	
	function isAvailable(date){
	    var dateAsString = date.getFullYear().toString() + "-" + (date.getMonth()+1).toString() + "-" + date.getDate();
	    var result = $.inArray( dateAsString ) ==-1 ? [true] : [false];
	    return result
	}
	
	function noWeekendsOrHolidays(date) {
	    var noWeekend = $.datepicker.noWeekends(date);
	    if (noWeekend[0]) {
	        return isAvailable(date);
	    } else {
	        return noWeekend;
	    }
	}
	
	function getMinDate(){
		var now = new Date();
		var outHour = now.getHours();
		if(parseInt(outHour) > 11){
			return '1D';
		}else{
			return '0D';
		}
	}
	
	$('input[name="parcel_pickup_date"]').datepicker({
			minDate: getMinDate(),
			maxDate: '7D',
			beforeShowDay: $.datepicker.noWeekends,
			dateFormat: 'yy-mm-dd',
			firstDay: 1
			
	});
	
	
});