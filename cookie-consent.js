function checkCookieConsent() {
    const consent = localStorage.getItem('cookieConsent');
    
    if (consent === null) return null;
    
    return consent === 'accepted';
  }
  

console.log(checkCookieConsent());

if (checkCookieConsent() === null) {
    const popup = document.getElementById('cookie-popup');
    popup.style.display = 'block';
  
    document.getElementById('accept-cookies').onclick = () => {
      localStorage.setItem('cookieConsent', 'accepted');
      document.cookie = "cookies_accepted=1; max-age=2592000; path=/";
      popup.style.display = 'none';
    };
  
    document.getElementById('reject-cookies').onclick = () => {
      localStorage.setItem('cookieConsent', 'rejected');
      popup.style.display = 'none';
    };
}
  
const buttondeletecookie = document.getElementById("deletecookiebutton");

buttondeletecookie.addEventListener("click", ()=>{ 
	const cookies = document.cookie.split(".");
	cookies = cookies[0].split(";")
	for (const cookie of cookies){
		const [name] = cookie.trim().split("=");
		document.cookie = `${name}=; expires=Thu; 01 Jan 1970 00:00:00 UTC; path=/;`;
	}
});
  
  
