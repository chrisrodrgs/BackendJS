// 7. Escreva um programa que calcule a área de um círculo a partir do raio, utilizando a
// fórmula: area = Math.PI * raio2

//Importante: necessário que seja um número positivo para o cálculo da área.

let raio;
while (true) {
    raio = Number(prompt('Insira um número de raio positivo:'));
    if (!isNaN(raio) && raio > 0) break;
    alert('Número inválido! Tente novamente.');
}

const areadocirculo = (Math.PI * (raio * raio));

resultadaarea.innerHTML = `O valor inserido do raio é: ${raio} <br>
O resultado do cálculo da área é: ${areadocirculo.toFixed(0)}`;


