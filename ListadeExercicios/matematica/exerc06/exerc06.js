// Crie um programa que calcule e exiba o perímetro de um círculo, solicitando o raio ao
// usuário. Utilize Math.PI para o cálculo

//Calculo do perímetro: P = 2 * pi(Math.PI) * r(raio)
//Importante: Necessário receber somente números e números posítivos. Não existe calculo de área negativo.

let raio;
while (true) {
    raio = Number(prompt('Insira um número de raio positivo:'));
    if (!isNaN(raio) && raio > 0) break;
    alert('Número inválido! Tente novamente.');
}

const perimetro = (2*Math.PI*raio);

resultadoperimetro.innerHTML = `O valor inserido do raio é: ${raio} <br>
O resultado do cálculo do perímetro é: ${perimetro.toFixed(0)}`;
 


