// // Função para verificar a estação do ano 


// // Elabore uma função que verifica a estação do ano conforme o mês. O usuário deverá inserir
// // um mês em um campo no html do tipo input (não usar o prompt). Com o valor inserido você
// // deverá verificar qual é a estação do ano do referido mês. Caso o valor informado não
// // corresponda a um mês válido, deve imprimir na página html "Valor inválido". Por fim, caso o
// // valor seja válido, apresente na página o resultado


// //Abaixo são listadas as estações e os seus respectivos meses.




// function verificarEstacao() {
//     const inverno = ['junho', 'julho', 'agosto'];
//     const primavera = ['setembro', 'outubro', 'novembro'];
//     const verao = ['dezembro', 'janeiro', 'fevereiro'];
//     const outono = ['março', 'abril', 'maio'];


function verificarEstacao() {
    const inverno = ['junho', 'julho', 'agosto'];
    const primavera = ['setembro', 'outubro', 'novembro'];
    const verao = ['dezembro', 'janeiro', 'fevereiro'];
    const outono = ['março', 'abril', 'maio'];
    
    const todosMeses = [...inverno, ...primavera, ...verao, ...outono];
    
    const mes = document.getElementById('mesInput').value.trim().toLowerCase();
    const resultadoDiv = document.getElementById('resultado');
    
    if (!todosMeses.includes(mes)) {
        resultadoDiv.innerHTML = 'Valor inválido';
        return;
    }
    
    let estacao = '';
    let gifUrl = '';
    
    if (inverno.includes(mes)) {
        estacao = 'Inverno ❄️';
        gifUrl = 'https://media1.tenor.com/m/Tg90zKIjCf8AAAAC/snow-jonsnow.gif';
    } else if (primavera.includes(mes)) {
        estacao = 'Primavera 🌸';
        gifUrl = 'https://media1.tenor.com/m/0D8Wvn3t50kAAAAC/spongebob-squarepants-spring.gif';
    } else if (verao.includes(mes)) {
        estacao = 'Verão ☀️';
        gifUrl = 'https://media1.tenor.com/m/fC2hF0Qsd9gAAAAC/angry-heat.gif';
    } else if (outono.includes(mes)) {
        estacao = 'Outono 🍂';
        gifUrl = 'https://media1.tenor.com/m/oZeggLy3sGEAAAAC/rainy-day-autumn.gif';
    }
    
    resultadoDiv.innerHTML = `
        <h2>Estação: ${estacao}</h2>
        <img src="${gifUrl}" alt="${estacao}" width="300">
    `;
}