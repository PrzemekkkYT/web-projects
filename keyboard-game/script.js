document.addEventListener("keydown", keyDown);
var kg_cells = document.getElementsByClassName("kg-cell");

var selectedCell = 0;
var treasureCell = 0;

var timerSpeed = 1;
var timerWidthStart = 1000;
var timerWidth = 1000;
var timerRunning = false;
var gameover = false;


function newTreasureCell(optSize) {
    treasureCell = Math.floor(Math.random() * ((Math.pow(optSize, 2)-1) - 0)) + 0;
    if (treasureCell == selectedCell) {
        newTreasureCell(optSize);
    }
    timerWidth += (timerWidthStart/10)*Math.sqrt(timerSpeed);
    if (timerWidth>timerWidthStart) {
        timerWidth = timerWidthStart;
    }
    timerSpeed = parseInt(points.innerHTML)/10+1;
    console.log("treasureCell: "+treasureCell.toString());
}

function selectCell(cell) {
    for (var i=0; i<kg_cells.length; i++){
        kg_cells[i].style.backgroundColor = null;
        kg_cells[treasureCell].style.backgroundColor = "#ff0000";
    }
    kg_cells[cell].style.backgroundColor = "#00ff00";
    selectedCell = cell;
}


function keyDown(e) {
    var optionSize = mapSize.options[mapSize.selectedIndex].index+3;
    console.log(e.code);
    switch (e.code) {
        case "ArrowUp":
            if (selectedCell-optionSize >= 0) {
                selectCell(selectedCell-optionSize);
            } else {
                selectCell(selectedCell+optionSize*(optionSize-1));
            }
            break;
        case "ArrowDown":
            if (selectedCell+(mapSize.options[mapSize.selectedIndex].index+3) < kg_cells.length) {
                selectCell(selectedCell+optionSize);
            } else {
                selectCell(selectedCell-optionSize*(optionSize-1));
            }
            break;
        case "ArrowLeft":
            if (selectedCell%optionSize > 0) {
                selectCell(selectedCell-1);
            } else {
                selectCell(selectedCell+optionSize-1);
            }
            break;
        case "ArrowRight":
            if (selectedCell%optionSize != (optionSize-1)) {
                selectCell(selectedCell+1);
            } else {
                selectCell(selectedCell-optionSize+1);
            }
            break;
        case "KeyP":
            timerSpeed+=1;
            break;
        case "KeyO":
            timerSpeed-=1;
            break;
    }
    if (selectedCell == treasureCell) {
        points.innerHTML = parseInt(points.innerHTML) + 1;
        newTreasureCell(optionSize);
        selectCell(selectedCell);
    }
}

var cells = document.getElementById("cells");

function clicked(){
    console.log(kg_cells);
    for (var i=0; i<kg_cells.length; i++){
kg_cells[i].style.backgroundColor = "red";
    }
    selectCell(4);
}

function selected() {
    console.log(mapSize.options[mapSize.selectedIndex].index);
    cells.style.width = ((mapSize.options[mapSize.selectedIndex].index+3)*120).toString();
    cells.style.height = ((mapSize.options[mapSize.selectedIndex].index+3)*120).toString();
    document.querySelectorAll('.kg-cell').forEach(e => e.remove());
    document.querySelectorAll('.gameover').forEach(e => e.remove());
    console.log(Math.pow(mapSize.options[mapSize.selectedIndex].index+3, 2));
    for (var i=0; i<Math.pow(mapSize.options[mapSize.selectedIndex].index+3, 2); i++) {
        var el = document.createElement("div");
        el.className = "kg-cell";
        el.innerHTML = i;
        cells.appendChild(el);
    }
    newTreasureCell(mapSize.options[mapSize.selectedIndex].index+3);
    selectCell(Math.floor(Math.random() * ((Math.pow(mapSize.options[mapSize.selectedIndex].index+3, 2)-1) - 0)) + 0);
    points.innerHTML = 0;
    timerWidth = timerWidthStart;
    timerSpeed = 1;
    gameover = false;
    timerBar();
}

function timerBar() {
    if(!timerRunning) {
        timerWidth = timerWidthStart;
        clearInterval(id);
        var timerbar = document.getElementById("timerbar");
        var id = setInterval(frame, 10);
        function frame() {
            if (timerWidth <= 0) {
                clearInterval(id);
                gameOver();
            } else {
                timerWidth-=timerSpeed;
                timerbar.style.width = timerWidth/10 + "%";
            }
        }
        timerRunning = true;
    }
}

function reload() {
    location.reload();
}

function gameOver() {
    if (!gameover) {
        document.querySelectorAll('.kg-cell').forEach(e => e.remove());
        var el = document.createElement("a");
        el.innerHTML = "GAME OVER\n\nKLIKNIJ ABY GRAĆ PONOWNIE";
        el.className = "gameover";
        el.href = "";
        el.onclick = "reload()";
        cells.appendChild(el);
        var el2 = document.createElement("a");
        el2.innerHTML = "Wynik: "+parseInt(points.innerHTML);
        el2.className = "gameover";
        cells.appendChild(el2);
        timerRunning = false;
        gameover = true;
    }
}