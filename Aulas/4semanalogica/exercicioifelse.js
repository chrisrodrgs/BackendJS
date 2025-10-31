/*Exercícios

Elabore um algorítimo que receba 4 notas com valores entre zero e 10.

Calcule a média das notas e imprima:

Aprovado se a média for maior que 7 pontos

Recuperação se a média for maior que 5 e menor que 7

Reprovado se a nota for menor que 5*/

const valor1 = 0;
const valor2 = 5;
const valor3 = 8;
const valor4 = 10;

const mediadasnotas = (valor1+valor2+valor3+valor4)/4;
console.log ('O valor da média das notas é: ', mediadasnotas)

if (mediadasnotas >= 7) {
    console.log ('Aprovado')

} else if (mediadasnotas >=5 && mediadasnotas <7) {
    console.log ('Você está de recuperação');
} else {
    console.log ("Reprovado");
}
