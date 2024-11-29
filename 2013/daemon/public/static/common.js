
(function(){var i,n,elements;if(!document.addEventListener){return;}
elements=document.getElementsByTagName('input');for(i=0,n=elements.length;i<n;i+=1){elements[i].addEventListener('focus',elements[i].select,false);}
elements=document.getElementsByTagName('textarea');for(i=0,n=elements.length;i<n;i+=1){elements[i].addEventListener('focus',elements[i].select,false);}}());