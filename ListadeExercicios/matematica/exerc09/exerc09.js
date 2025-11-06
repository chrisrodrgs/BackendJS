// 9. Escreva um programa que receba a largura e o comprimento de um retângulo, calcule o
// perímetro e a área dele utilizando as fórmulas:
// perimetro = 2 * (largura + comprimento)
// area = largura * comprimento

let largura;
while (true) {
    largura = Number(prompt('Insira um número de largura positivo:'));
    if (!isNaN(largura) && largura > 0) break;
    alert('Número inválido! Tente novamente.');
}

let comprimento;
while (true) {
    comprimento = Number(prompt('Insira um número de comprimento positivo:'));
    if (!isNaN(comprimento) && comprimento > 0) break;
    alert('Número inválido! Tente novamente.');
}

//Cálculo do perímetro
const perimetro = 2*(largura+comprimento);


//Cálculo da área
const area = largura * comprimento;

//Resultados
resultados.innerHTML = `O valor da largura inserido foi: ${largura} <br>
O valor do comprimento inserido foi: ${comprimento} <br>
O resultado do cálculo do perímetro é: ${perimetro.toFixed(0)} <br>
O resultado do cálculo da área é: ${area.toFixed(0)}`;