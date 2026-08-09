<script>
document.addEventListener('DOMContentLoaded', function() {
const menuWrapper = document.querySelector('.menu-button');
if (menuWrapper) {
menuWrapper.addEventListener('click', function() {
this.classList.toggle('is-active');
});
}
});
</script>