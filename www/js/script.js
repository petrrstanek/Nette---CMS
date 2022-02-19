const collapse = document.getElementById('sidebarCollapse');
const sidebar = document.getElementById('sidebar');
collapse.addEventListener('click', () => {
	sidebar.classList.toggle('active');
});

const editor = new FroalaEditor('#editor');

const favIcons = document.querySelectorAll('.fav');

favIcons.forEach(favIcon => {
	console.log(favIcon)
	favIcon.addEventListener('click', function () {
		favIcon.classList.toggle('active');
		console.log('added')
	})
});