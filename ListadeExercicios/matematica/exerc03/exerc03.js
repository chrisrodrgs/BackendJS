// 3. Crie um programa que calcule e exiba a média aritmética de três notas informadas pelo
// usuário (utilize o prompt e não se esqueça de importar o arquivo js no html).

const nota1 = Number(prompt('Informe a primeira nota:'));
const nota2 = Number(prompt('Informe a segunda nota:'));
const nota3 = Number(prompt('Informe a terceira nota:'));

const media = (nota1+nota2+nota3) / 3;

resultadomedia.innerHTML = `As notas informadas foram: ${nota1}, ${nota2}, ${nota3} <br>
O resultado da média das três notas é: ${media}`;