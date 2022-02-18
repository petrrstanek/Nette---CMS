const collapse = document.getElementById('sidebarCollapse');
const sidebar = document.getElementById('sidebar');
collapse.addEventListener('click', () => {
	sidebar.classList.toggle('active');
});

const editor = new FroalaEditor('#editor');

let today = new Date();
const months = [
	'Ledna',
	'Února',
	'Března',
	'Dubna',
	'Května',
	'Června',
	'Července',
	'Srpna',
	'Září',
	'Října',
	'Listopadu',
	'Prosince',
];
let date = today.getDate() + '.' + (today.getMonth() + 1) + '.' + today.getFullYear();
let minutes = today.getMinutes();
let sec = today.getSeconds();
if (minutes < 10) {
	minutes = '0' + minutes;
}
if (sec < 10) {
	sec = '0' + sec;
}
let time = today.getHours() + ':' + minutes + ':' + sec;
let dateTime = `${date} - ${time}`;

//Zakázat vstup pro datum
const field = document.getElementById('time');
field.setAttribute('value', dateTime);
field.readOnly = true;

if ('Edit:edit') {
	const selectButtons = document.querySelector('.selectButtons');
	let value;
	for (let i = 0; i < selectButtons.length; i++) {
		if (selectButtons[i].selected) {
			selectButtons.setAttribute('readonly', 'readonly');
		}
	}
}
