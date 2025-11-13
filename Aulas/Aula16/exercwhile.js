// Exercício 1 – Estrutura while com break e continue


// Crie um programa que percorra um array de números inteiros e exiba apenas os
// números positivos, interrompendo o loop se encontrar o número 0.


const numeros = [3, -5, 7, 10, -2, 0, 12, 8];
let i = 0;

while(i < numeros.length){
    if ( numeros[i] > 0){
        console.log(numeros[i]);
    }
    else if(numeros[i] === 0){
        console.log('Número zero (0) encontrado. Saindo do laço.');
        break
    
    }
    i++; //PARA SAIR DO LAÇO
}
