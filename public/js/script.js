const container = document.getElementById('images');
const images = [];
let isAdding = false;

window.onload = async () => {
    clearContainer();
    loadImages();
}

async function loadImages() {
    images.length = 0;
    let page = 1;
    let imagesOnPage = [];
    do {
        let response = await fetch(`/api/images?page=${page++}`)

        if (!response.ok) {
            console.error("Response is not ok.");
            return [];
        }
        imagesOnPage = await response.json();
        imagesOnPage.forEach((image) => {
            renderImage(image);
        })

        if (!isAdding) {
            hideLoader();
        }
    } while (imagesOnPage.length !== 0)
    return images;
}

function clearContainer() {
    container.innerHTML = '';
}

function renderImage(imageObject, append = true) {
    const img = new Image();
    img.src = imageObject.image;
    img.onload = () => {
        const imageDiv = document.createElement('div');
        imageDiv.classList.add('image-item');
        imageDiv.appendChild(img);

        if (append) {
            container.appendChild(imageDiv);
        } else {
            container.insertBefore(imageDiv, container.firstChild)
        }
    }
}

async function sendRequest() {
    const url = document.getElementById('pageUrl').value;
    const minWidth = parseInt(document.getElementById('minWidth').value);
    const minHeight = parseInt(document.getElementById('minHeight').value);
    const overlayText = document.getElementById('overlayText').value;

    isAdding = true;
    showLoader();
    const response = await fetch(`/api/images/scrap`, {
        'method': 'POST',
        headers: {
            'Accept': 'application/json',
            'Content-Type': 'application/json'
        },
        'body': JSON.stringify({
            'url': url,
            'minWidth': minWidth,
            'minHeight': minHeight,
            'text': overlayText
        })
    })
    hideLoader();
    isAdding = false;

    if (!response.ok) {
        console.error("Ошибка при добавлении", (await response.text()));
        return;
    }
    let addedImages = await response.json();
    images.unshift(...addedImages);
    addedImages.forEach((image) => {
        renderImage(image, false);
    })
}

function hideLoader() {
    document.getElementById('overlay-loader').style.display = 'none';
}

function showLoader() {
    document.getElementById('overlay-loader').style.display = 'flex';
}