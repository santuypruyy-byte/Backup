(function(){
    const sidebar=document.getElementById('sidebar');
    const menu=document.querySelector('.menu-btn');
    window.toggleSidebar=function(){if(sidebar)sidebar.classList.toggle('open');};
    document.addEventListener('click',function(e){
        if(window.innerWidth<=850&&sidebar&&sidebar.classList.contains('open')&&menu&&!sidebar.contains(e.target)&&!menu.contains(e.target))sidebar.classList.remove('open');
    });
    window.addEventListener('resize',function(){if(window.innerWidth>850&&sidebar)sidebar.classList.remove('open');});
})();
