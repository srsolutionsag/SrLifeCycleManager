/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */

$(document).ready(function () {
	$('.sr-async-loader').each(function () {
		let item = $(this);
		let url = decodeURI(item.data("asyncUrl"));

		$.get(url, function (data) {
			let element = $.parseHTML(data);
			item.html(element)
		});
	});
});