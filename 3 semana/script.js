//Crie uma string chamada frase que contenha aspas dentro da string.

const frase = 'Olá, boa noite. Este exercício é muito "fácil".';
console.log(frase);

/*Crie duas variáveis nome e cidade com valores à sua escolha e concatene-as em uma frase
completa usando o operador +.*/

let nome = "Christian ";
let cidade = "Ouro Preto";

const nomeCidade = nome + cidade;
console.log(nomeCidade);

/*Crie uma string que utilize caracteres de escape (\n, \t) para formatar uma mensagem de duas
linhas com tabulação.*/

const frase2 = `Olá. Está é a primeira linha.\nEstá é a segunda linha.\tAqui, após o espaço.`;
console.log(frase2);

/*Crie uma string usando Template String para incluir variáveis e expressões dentro da string de
forma dinâmica.*/

const frase3 = `O meu nome é ${nome} e moro na cidade ${cidade}.`;
console.log(frase3);

/*Aplique pelo menos três métodos de string em qualquer uma das variáveis criadas:
toUpperCase(), toLowerCase(), length, replace(), includes(), etc.*/

const 