console.log('Script is work')

class StateVisibility_Indiv_content{

    constructor(list, content, creator, mystorys, searchStoryes){
        this.list = list;
        this.content = content;
        this.creator = creator;
        this.mystoryes = mystorys;
        this.searchStoryes = searchStoryes;
    }

    visListStory() {
        this.list.style.display = 'block';
        this.content.style.display = 'none';
        this.creator.style.display = 'none';
        this.mystoryes.style.display ='none';
        this.searchStoryes.style.display ='none';
    }

    visContent(){
        this.list.style.display = 'none';
        this.content.style.display = 'block';
        this.creator.style.display = 'none';
        this.mystoryes.style.display ='none';
        this.searchStoryes.style.display ='none';
    }

    vidCreator(){
        this.list.style.display = 'none';
        this.content.style.display = 'none';
        this.creator.style.display = 'block';
        this.mystoryes.style.display ='none';
        this.searchStoryes.style.display ='none';
    }
    vidMyStory(){
        this.list.style.display = 'none';
        this.content.style.display = 'none';
        this.creator.style.display = 'none';
        this.mystoryes.style.display = 'block';
        this.searchStoryes.style.display ='none';
    }
    vidSearchStory(){
        this.list.style.display = 'none';
        this.content.style.display = 'none';
        this.creator.style.display = 'none';
        this.mystoryes.style.display ='none';
        this.searchStoryes.style.display = 'block';
    }

    blockAll(){
        this.list.style.display = 'none';
        this.content.style.display = 'none';
        this.creator.style.display = 'none';
        this.mystoryes.style.display ='none';
        this.searchStoryes.style.display = 'none';        
    }
}

const catalogLine = document.getElementById('catalogLink');
const storyListContainer = document.getElementById('storyListContainer');
const storyContentContainer = document.getElementById('storyContentContainer');
const storyCreator = document.getElementById('creatorStory');
const searchStoryes = document.getElementById("searchStoryes");
const myStoryes = document.getElementById("MyStoryes");

const firstAuhtor = document.getElementById('firstAuhtor');
const historyCreator = document.getElementById('historyCreator');

const searchAuthtoButton = document.getElementById("searchAuthtoButton");

const postStory = document.getElementById('postStory');

const storyDivUkosatel = document.querySelectorAll(".storyDivUkosatel");

const StateVisibilityContener = new StateVisibility_Indiv_content(storyListContainer, storyContentContainer, storyCreator, myStoryes, searchStoryes);
StateVisibilityContener.blockAll();

const listStory = document.getElementById("listStory");

listStory.addEventListener('click', function(e) { 
    StateVisibilityContener.vidMyStory();
});

document.querySelectorAll(".deleteStoryes").forEach(div => {
            div.addEventListener('click', function() { 
                const itemId = this.getAttribute('data-id');
                showStoryContent(itemId);

                const data = {
                    action: '1',
                    id: itemId
                };

                fetch('storyControl.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(data)
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Ошибка сети: ' + response.status);
                    }
                    return response.text().then(text => {
                        try {
                            return text ? JSON.parse(text) : {};
                        } catch (e) {
                            console.error('Ошибка парсинга JSON:', text);
                            throw new Error('Невалидный JSON: ' + text);
                        }
                    });
                })
                .then(data => {
                    console.log('Success:', data);
                    if (data.status === 1) {
                        alert('История успешно удалена');
                        location.reload(true);
                    } else {
                        alert('Произошла ошибка при удалении истории');
                    }
                })
                .catch((error) => {
                    console.error('Error:', error);
                    alert('Произошла ошибка при отправке запроса: ' + error.message);
                });
});});

function addStoryClickHandlers(){
    document.querySelectorAll(".storyDivUkosatel").forEach(div => {
            div.addEventListener('click', function() { 
                const itemId = this.getAttribute('data-id');
                showStoryContent(itemId);
            } ); 
        });
}

searchAuthtoButton.addEventListener('click', function(e) { 

    const username = document.getElementById('searchAuthtoInput').value;

    fetch(`?search_story=${username}`)
        .then(response => response.json())
        .then(data => { 
        if (data.success) {
            const container = document.getElementById('searchStoryes');
            container.innerHTML = '';

            const stories = data.stories;

            stories.forEach(content => {
                const div = document.createElement('div');
                div.className = 'storyDivUkosatel';
                div.style.marginBottom = '9px';
                div.style.marginTop = '9px';
                div.dataset.id = content.id;
                
                const h2 = document.createElement('h2');
                h2.textContent = content.title;
                
                const p = document.createElement('p');
                p.style.fontSize = '18px';
                p.innerHTML = `<em>Автор: ${content.author_name}</em>`;
                
                div.appendChild(h2);
                div.appendChild(p);
                container.appendChild(div);
            });

            StateVisibilityContener.vidSearchStory();
            addStoryClickHandlers();
        }
        
        })
        .catch(error => {
        });
});

document.querySelectorAll(".storyDivUkosatel").forEach(div => {
            div.addEventListener('click', function() { 
                const itemId = this.getAttribute('data-id');
                showStoryContent(itemId);
            } ); 
        });

postStory.addEventListener('click', function(e) {
     //storyControl.php

    const topik = document.getElementById('storyTopickСreator').value;
    const story = document.getElementById('storyTextСreator').value;

    if (topik == '' || story == ''){
        alert('Пустые поля');
    }

    const data = {
        action: '-1',
        topik: topik,
        story: story
    };

    const xhr = new XMLHttpRequest();
    xhr.open('POST', 'storyControl.php', true);
    xhr.setRequestHeader('Content-Type', 'application/json');

    xhr.onload = function() {
        if (xhr.status >= 200 && xhr.status < 300) {
            const response = JSON.parse(xhr.responseText);
            if (response.status == -1){
                alert('Ошибка, история не добавленна!');
            } if (response.status == 1) {
                alert('История добавленна!');
                location.reload(true);
            } else {
                console.log('Success:', response);
                alert('Данные успешно обновлены!');
            }
        } else {
            console.error('Error:', xhr.statusText);
            alert('Произошла ошибка: ' + xhr.statusText);
        }
    };

    xhr.onerror = function() {
        console.error('Request failed');
        alert('Произошла ошибка сети');
    };

    xhr.send(JSON.stringify(data));

});

historyCreator.addEventListener('click', function(e) {
    StateVisibilityContener.vidCreator(); //storyControl.php
});

function showStoryList() {
    StateVisibilityContener.visListStory();
}

function showStoryContent(storyId) {
    fetch(`?item_id=${storyId}`)
        .then(response => response.json())
        .then(data => { 
        if (data.error) {
            StateVisibilityContener.visListStory();
        } else {
            document.getElementById('storyTitle').textContent = data.title;
            document.getElementById("aothorStoryAbout").textContent = data.author_about;
            document.getElementById("aothorStoryUsername").textContent = data.author_name;
            document.getElementById('storyText').innerHTML = formatStoryText(data.story);

            if (localStorage.getItem('cookieConsent') === 'accepted') {
                document.cookie = `lastStoryId=${data.id}; max-age=${60*60*24*7}; path=/`;
            }

            StateVisibilityContener.visContent();
        }
        })
        .catch(error => {
        });
}

function formatStoryText(text) {
    const styles = {
        paragraph: 'font-size: 20px; line-height:1.6; margin:0 0 15px 0;',
        bold: 'font-weight:bold;',
        italic: 'font-style:italic;'
    };
    
    return text.split('\n\n').map(paragraph => {
        // Добавляем базовое форматирование
        let formatted = paragraph
            .replace(/\*\*(.*?)\*\*/g, `<span style="${styles.bold}">$1</span>`)
            .replace(/\*(.*?)\*/g, `<span style="${styles.italic}">$1</span>`)
            .replace(/\n/g, '<br>');
            
        return `<p style="${styles.paragraph}">${formatted}</p>`;
    }).join('');
}


catalogLine.addEventListener('click', function(e) {
    e.preventDefault();
    showStoryList();
});

function checkCookies() {
    
    if (window.location.hash === '#Catalog') {
        showStoryList();
        return;
    }

    const cookies = document.cookie.split(';').map(c => c.trim());
    const lastStoryCookie = cookies.find(c => c.startsWith('lastStoryId='));
    
    if (lastStoryCookie) {
        const lastStoryId = parseInt(lastStoryCookie.split('=')[1]);
        showStoryContent(lastStoryId);
    } else {
        showStoryList();
    }
}

document.addEventListener('DOMContentLoaded', checkCookies);