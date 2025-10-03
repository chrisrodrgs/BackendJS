const nome = 'Christian';
const idade = 28;
const altura = 1.84;
const estudante = true;
const peso = 88;
const imc = peso/altura**2;

console.log (`Meu nome é: ${nome}, tenho ${idade} anos e minha altura é ${altura} metros.`);
console.log (`O dobro da minha idade é: ${idade*2}`);
console.log (`A metade da minha altura é: ${altura/2}`);
console.log (`O meu IMC é: ${imc}`);
console.log (`Sou estudante: ${estudante}`);
console.log (`O meu IMC é: ${imc.toFixed(2)}`); //toFixed para definir a quantidade de casas decimais