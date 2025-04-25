window.addEventListener('DOMContentLoaded', () => {
    // Login
    const username = document.getElementById('username');
    const password = document.getElementById('password');
    
    if (username && password) {
        username.setCustomValidity("Missing username!!");
        password.setCustomValidity("Missing password!!");
        
        username.addEventListener('input', () => {
            username.setCustomValidity('');
        });
        
        password.addEventListener('input', () => {
            password.setCustomValidity('');
        });
        
        const form = document.getElementById('loginForm');
        if (form) {
            form.addEventListener("submit", function(event){
                let isValid = true;
                
                if (username.validity.valueMissing){
                    isValid = false;
                }else{
                    username.setCustomValidity("");
                }
                if (password.validity.valueMissing){
                    isValid = false;
                }else{
                    password.setCustomValidity("");
                }
                
                if(!isValid){
                    event.preventDefault();
                }
            });
        }
    }
    
    // Search
    const searchLine = document.getElementById('genreSearchLine');
    const genreButton = document.getElementsByClassName('genre');
    // console.log("搜索按钮元素:", genreButton);
    // console.log("搜索框元素:", searchLine);
    // Search line
    if (searchLine) {
        searchLine.addEventListener('keydown', function(event) {
            // console.log("按键事件触发:", event.key);
            if (event.key === 'Enter') {
                event.preventDefault();
                const searchValue = document.getElementById('genreSearchLine').value;
                // console.log("搜索值:", searchValue);
                if (/^\s*$/.test(searchValue)) {
                    // console.log("无效搜索值");
                    return;
                }
                searchForTitle(searchValue);
            }
        });
    } else {
        console.error("找不到搜索框元素!");  
    }

    // Search button
    if (genreButton) {
        for (let i = 0; i < genreButton.length; i++) {
            // console.log("为按钮", i, "添加事件监听器");  
            genreButton[i].addEventListener('click', function() {
                // console.log("按钮", i, "被点击");  
                const searchValue = genreButton[i].innerText;
                // console.log("搜索值:", searchValue);  
                searchForTitle(searchValue);
            })
        } 
    }else{
        console.error("找不到搜索按钮元素!");  
    }

    // Play music
    const playButton = document.getElementsByClassName('play');
    const pauseButton = document.getElementsByClassName('pause');
    const continueButton = document.getElementsByClassName('continue');
    const elems = document.getElementsByClassName("musicAudio");
    const isPaused = document.getElementsByClassName("isPause");

    if (playButton) {
        for (let i = 0; i < playButton.length; i++) {
           
            playButton[i].addEventListener('click', function() {
                const musicId = playButton[i].nextElementSibling.nextElementSibling.nextElementSibling.innerHTML;
                console.log("音频ID:", musicId);
                var httpRequest = new XMLHttpRequest();
                if (!httpRequest) {
                    alert('Giving up :( Cannot create an XMLHTTP instance');
                    return false;
                }
                httpRequest.onreadystatechange = function() {
                    if (httpRequest.readyState === XMLHttpRequest.DONE && httpRequest.status === 200) {
                        var elem = elems[i];
                        elem.innerHTML = httpRequest.responseText;
                        elem.play();
                        playButton[i].style.display = "none";
                        pauseButton[i].style.display = "block";
                        continueButton[i].style.display = "none";
                    }else if (httpRequest.readyState === XMLHttpRequest.DONE && httpRequest.status !== 200) {
                        window.location.href = 'index.php?error=session_expired';
                    }
                }
                httpRequest.open('GET', 'file.php?musid=' + musicId);
                httpRequest.send();
            });
            pauseButton[i].addEventListener('click', function() {
                var elem = elems[i];
                elem.pause();
                playButton[i].style.display = "none";
                pauseButton[i].style.display = "none";
                continueButton[i].style.display = "block";
                isPaused[i].style.display = "block";
            }) 
            continueButton[i].addEventListener('click', function() {
                var elem = elems[i];
                elem.play();
                playButton[i].style.display = "none"; 
                pauseButton[i].style.display = "block";
                continueButton[i].style.display = "none";
                isPaused[i].style.display = "none";
            }) 
            
            elems[i].addEventListener('ended', function() {
                playButton[i].style.display = "block";
                pauseButton[i].style.display = "none";
                continueButton[i].style.display = "none";
                this.removeChild(this.firstChild);
                isPaused[i].style.display = "none";
            })
        }
    }
});

function searchForTitle(searchValue){
    console.log("searchForTitle函数被调用");  
    var httpRequest = new XMLHttpRequest();
    if (!httpRequest) {
        alert('Giving up :( Cannot create an XMLHTTP instance');
        return false; 
    }
    
    console.log("函数内搜索值:", searchValue);  
    
    httpRequest.onreadystatechange = function() {
        if (httpRequest.readyState === XMLHttpRequest.DONE) {
            if (httpRequest.status === 200) {
                console.log("执行成功！！！")
                // 刷新页面显示搜索结果
                if (searchValue!= "Popular"){
                    window.location.href = 'index.php?Search=' + searchValue;
                }else{
                    window.location.href = 'index.php';
                }
            } else {
                // 处理非200状态码，明确是会话超时或其他错误
                console.error('请求失败，状态码:', httpRequest.status); // 记录具体状态码
                // 重定向到 index.php 并附带错误信息
                window.location.href = 'index.php?error=session_expired'; // 修改这里
            }
        }
    };
    
    if (searchValue != "Popular"){
        httpRequest.open('GET', 'index.php?Search=' + searchValue);
    }else{
        httpRequest.open('GET', 'index.php');
    }
    httpRequest.send();
}

