// Small admin utilities
document.addEventListener('click', function(e){
  if(e.target.matches('.confirm-delete') || e.target.closest('.confirm-delete')){
    var btn = e.target.closest('.confirm-delete');
    if(!confirm('Are you sure you want to delete this item? This action cannot be undone.')){
      e.preventDefault();
      return false;
    }
  }
});

// show/hide edit forms by id
function showEditFormById(id){
  var el = document.getElementById(id);
  if(el) el.style.display = 'block';
}

// Simple helper to initialize admin page class on body
document.addEventListener('DOMContentLoaded', function(){
  document.body.classList.add('admin-page');
});
