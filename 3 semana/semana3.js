/* Aspas dentro de strings
Concatenação
Caracteres de escape
Template Strings (ou Template Literals)
Métodos de String
*/


let frase1 = 'olá, boa noite! Estou estudando "Javascript"';
let frase2 = 'olá, boa noite! Estou estudando \'Javascript\'';
let frase3 = "olá, boa noite! Estou estudando \"Javascript\"";
let frase4 = "Olá, boa noite! Estou estudando 'Javascript'";
let frase5 = `Olá, boa noite! \nEstou estudando "Javascript"\n`; 

//Quando coloco um \n na frase, ele quebra a linha e envia o restante do texto para a linha de baixo//
console.log(frase1);
console.log(frase2);
console.log(frase3);
console.log(frase4);
console.log(frase5);

//Concatenação de strings

let nome ='Christian ' //colocar um espaço na frente do nome, para que isso apareça no texto com espaço
let sobrenome ='Rodrigues da Silva'
let numString = '2'
let nomeCompleto = nome + sobrenome
let idade = 28
//ou
nome += sobrenome
numString += idade


console.log(nome);
// console.log(numString === idade);
console.log

// Caracteres de escape

const endereco = 'Rua pandiá Calógeras, Ouro Preto\\MG';

console.log(endereco);

const bairro = 'Bauxita \nCEP: 02548-254';

console.log(bairro);

//Template String (ou Template Literals)
// Melhor forma!!! (´)

const enderecoCompleto = `${endereco}, 
\n${bairro} `; //Ele lê também os TAB, "enter"
console.log(enderecoCompleto);

