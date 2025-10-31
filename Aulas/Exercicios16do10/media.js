/*Crie uma função que receba a média final de um aluno e determine sua situação:

“Aprovado” se a média for maior ou igual a 7;

“Recuperação” se for entre 5 e 6.9;

“Reprovado” se for menor que 5.

 A função deve exibir a situação no console.*/

function MediaFinal(media) {
    if (media >= 7) return "Aprovado";
    if (media >= 5) return "Recuperação";
    return "Reprovado";


}
const media = 7;
console.log(MediaFinal(media));
