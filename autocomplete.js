YAHOO.widget.BasicRemote = function() {
	// Use an XHRDataSource
	var oDS = new YAHOO.util.XHRDataSource("data.php");
	// Set the responseType
	oDS.responseType = YAHOO.util.XHRDataSource.TYPE_TEXT;

	// Define the schema of the delimited results
	oDS.responseSchema = {
		recordDelim: "\n",
		fieldDelim: "\t"
	};
	// Enable caching
	oDS.maxCacheEntries = 5;

	// Instantiate the AutoComplete
	var oACEvent = new YAHOO.widget.AutoComplete("eventInput", "eventContainer", oDS);
		oACEvent.generateRequest = function(sQuery) {
		return "?query=" + sQuery + "&api=event";
	};
	var oACSchool = new YAHOO.widget.AutoComplete("schoolInput", "schoolContainer", oDS);
		oACSchool.generateRequest = function(sQuery) {
		return "?query=" + sQuery + "&api=school&classification=" + YAHOO.util.Dom.get("classification").value;
	};
	var oACLocation = new YAHOO.widget.AutoComplete("locationInput", "locationContainer", oDS);
		oACLocation.generateRequest = function(sQuery) {
		return "?query=" + sQuery + "&api=location";
	};
	var oACAthlete = new YAHOO.widget.AutoComplete("athleteInput", "athleteContainer", oDS);
		oACAthlete.generateRequest = function(sQuery) {
		return "?query=" + sQuery + "&api=athlete&school=" + YAHOO.util.Dom.get("schoolInput").value;
	};

	//define your itemSelect handler function:
	var schoolSelectHandler = function(sType, aArgs) {
		var oSchoolAcInstance = aArgs[0]; // your AutoComplete instance
		var elListItem = aArgs[1]; // the <li> element selected in the suggestion
	};

	var eventSelectHandler = function(sType, aArgs) {
		var oItemAcInstance = aArgs[0]; // your AutoComplete instance
		var elListItem = aArgs[1]; // the <li> element selected in the suggestion
		var oData = aArgs[2]; // object literal of data for the result
		var str = oItemAcInstance.getInputEl().value;
		if (str.indexOf('Diving') != -1) {
			YAHOO.util.Dom.get("timeSelector").style.display = "none";
			YAHOO.util.Dom.get("pointSelector").style.display = "block";
			YAHOO.util.Dom.get("minutes").value = "";
			YAHOO.util.Dom.get("seconds").value = "";
			YAHOO.util.Dom.get("millis").value = "";
		} else if (YAHOO.util.Dom.get("timeSelector").style.display == "none") {
			YAHOO.util.Dom.get("pointSelector").style.display = "none";
			YAHOO.util.Dom.get("timeSelector").style.display = "block";
			YAHOO.util.Dom.get("points").value = "";
		}
		if (str.indexOf('Relay') == -1) {
			YAHOO.util.Dom.get("athleteAutoComplete").style.display = "block";
		} else {
			YAHOO.util.Dom.get("athleteAutoComplete").style.display = "none";
			YAHOO.util.Dom.get("athleteInput").value = "";
		}
	};

	var athleteNoneHandler = function(sType, aArgs) {
		var oAthleteAcInstance = aArgs[0]; // your AutoComplete instance
		var elListItem = aArgs[1]; // the <li> element selected in the suggestion
		var gi = YAHOO.util.Dom.get("gradeInput");
		if (gi.style.display == "none") {
				gi.style.display = "block";
				YAHOO.util.Dom.get("grade").focus();
		}
	};

	var athleteSelectHandler = function(sType, aArgs) {
		var oAthleteAcInstance = aArgs[0]; // your AutoComplete instance
		var elListItem = aArgs[1]; // the <li> element selected in the suggestion
		YAHOO.util.Dom.get("gradeInput").style.display = "none";
	};

	//subscribe your handler to the event, assuming
	//you have an AutoComplete instance oACEvent:
	oACEvent.itemSelectEvent.subscribe(eventSelectHandler);

	//subscribe your handler to the event, assuming
	//you have an AutoComplete instance oACSchool:
	oACSchool.itemSelectEvent.subscribe(schoolSelectHandler);

	//subscribe your handler to the event, assuming
	//you have an AutoComplete instance oACAthlete:
	//oACAthlete.unmatchedItemSelectEvent.subscribe(athleteNoneHandler);
	//oACAthlete.itemSelectEvent.subscribe(athleteSelectHandler);

	return {
		oDS: oDS,
		oACEvent: oACEvent,
		oACSchool: oACSchool,
		oACAthlet: oACAthlete,
		oACLocation: oACLocation
	};

}();

YAHOO.namespace("wisca.calendar");
 
function handleSelect(type,args,obj) {   
	var dates = args[0];   
	var date = dates[0];   
	var year = date[0], month = date[1], day = date[2];   
	document.getElementById("eventdate").value = month + "/" + day + "/" + year;
}

YAHOO.wisca.calendar.init = function() {
	YAHOO.wisca.calendar.cal = new YAHOO.widget.Calendar("cal","calContainer");   
	var date = document.getElementById("eventdate").value;
	if (date) {
		YAHOO.wisca.calendar.cal.select(date);
		YAHOO.wisca.calendar.cal.setMonth(date.substring(0, date.indexOf('/')) - 1);
	}
	YAHOO.wisca.calendar.cal.render();
	YAHOO.wisca.calendar.cal.selectEvent.subscribe(handleSelect, YAHOO.wisca.calendar.cal, true);   
}

YAHOO.util.Event.onDOMReady(YAHOO.wisca.calendar.init);


