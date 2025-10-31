// 1. Escreva um programa que solicite ao usuário dois números e exiba a soma, subtração,
// multiplicação e divisão entre eles.

const num1 = Number(prompt('Informe o primeiro número'));
const num2 = Number(prompt('Informe o segundo número'));
// const num1 = 10;
// const num2 = 15;

const soma = (num1 + num2);
// console.log('Resultado da soma dos números', soma);

const subtracao = (num1 - num2);
// console.log('Resultado da subtração dos números', subtracao);

const multiplicacao = (num1 * num2);
// console.log('Resultado da multiplicação dos números', multiplicacao);

let divisao;
if (num2 === 0) {
    alert("Divisão por zero não é permitida.");
} else {
    divisao = num1 / num2;

}

resultado.innerHTML = `Os números informados foram: ${num1} e ${num2} <br>
A soma entre ${num1} e ${num2} é igual a ${soma} <br>
A subtração entre ${num1} e ${num2} é igual a ${subtracao} <br>
A multiplicação entre ${num1} e ${num2} é igual a ${multiplicacao} <br>
A divisão entre ${num1} e ${num2} é igual a ${divisao} <br>`;
