/*Desenvolva uma função que receba um número inteiro e determine se ele é par ou ímpar.O
resultado deve ser impresso no console informando o número e se ele é par ou ímpar.*/
function NumeroInt(numero) {
    if (numero % 2 === 0) return "Par";
    return "ímpar";
}

function NumeroInt2(numero) {
    if (numero % 2 === 1) return "Ímpar";
    return "Par";
}

const numero = 10;
console.log(NumeroInt(numero));
console.log(NumeroInt2(numero));

