const collapse = document.getElementById('sidebarCollapse');
const sidebar = document.getElementById('sidebar');
collapse.addEventListener('click', () => {
	sidebar.classList.toggle('active');
});

const editor = new FroalaEditor('#editor');

const favIcons = document.querySelectorAll('.fav-icon');

favIcons.forEach(favIcon => {
	console.log(favIcon)
	favIcon.addEventListener('click', function () {
		console.log('click')
	})
});


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