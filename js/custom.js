/*
 *     Licensed to UbiCollab.org under one or more contributor
 *     license agreements.  See the NOTICE file distributed 
 *     with this work for additional information regarding
 *     copyright ownership. UbiCollab.org licenses this file
 *     to you under the Apache License, Version 2.0 (the "License");
 *     you may not use this file except in compliance
 *     with the License. You may obtain a copy of the License at
 * 
 *     		http://www.apache.org/licenses/LICENSE-2.0
 *     
 *     Unless required by applicable law or agreed to in writing,
 *     software distributed under the License is distributed on an
 *     "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF ANY
 *     KIND, either express or implied.  See the License for the
 *     specific language governing permissions and limitations
 *     under the License.
 */
$(function() {
    $('#box').click(function(){
        $(this).animate({'top':'-200px'},500,function(){
            $('#overlay').fadeOut('fast');
        });
    });

});

$(function() {
    $('#gallery a').lightBox();
});

$(document).ready(function() {
	//When page loads...
	$(".tab_content").hide(); //Hide all content
	if(location.hash != "") {
		var target = "#"+location.hash.split("#")[1]
		$(location.hash).show(); //Show first tab content
		$("ul.tabs li:has(a[href="+target+"])").addClass("active").show();
	} else {
		$("ul.tabs li:first").addClass("active").show(); //Activate first tab
		$(".tab_content:first").show(); //Show first tab content
	}

	//On Click Event
	$("ul.tabs li").click(function() {
		$("ul.tabs li").removeClass("active"); //Remove any "active" class
		$(this).addClass("active"); //Add "active" class to selected tab
		$(".tab_content").hide(); //Hide all tab content

		var activeTab = $(this).find("a").attr("href"); //Find the href attribute value to identify the active tab + content
		$(activeTab).fadeIn(); //Fade in the active ID content
		return false;
	});
});

$(function () { // this line makes sure this code runs on page load
	$('#selectall').click(function () {
		$(this).parents($('input[type=submit]').closest("form")).find(':checkbox').attr('checked', this.checked);
	});
	
	$(this).find(':checkbox').click(function(){
        if($("input:checkbox").length == $("input:checked").length) {
            $("#selectall").attr("checked", true);
        } else {
            $("#selectall").attr("checked", false);
        }
	});
});

function rmItem()
{
    var answer = confirm("Are you sure?")
    if (answer){
        document.messages.submit();
        return false;
    }
    return false;  
} 

function doNav(theUrl)
{
	document.location.href = theUrl;
}

function suggest(inputString){
    if(inputString.length == 0) {
        $('#suggestions').fadeOut();
    } else {
    $('#user-text').addClass('load');
        $.post("modules/suggest.users.inc.php", {queryString: ""+inputString+""}, function(data){
            if(data.length >0) {
                $('#suggestions').fadeIn();
                $('#suggestionsList').html(data);
                $('#user-text').removeClass('load');
            }
        });
    }
}
 
function fill(thisValue) {
	$('#user-text').val(thisValue);
	setTimeout("$('#suggestions').fadeOut();", 600);
}

