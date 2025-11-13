// Estrutura do...while com break e continue


// Cadastro com validação de idade

// Implemente um programa que simule o cadastro de usuários em um sistema.

//  O programa deve solicitar idades simuladas (usando valores em um array) e:

// Ignorar (continue) cadastros de menores de 18 anos.

// Encerrar (break) quando encontrar um valor negativo (simulando cancelamento).

// Exibir todas as idades válidas cadastradas


const idades = [22, 17, 35, 15, 40, -1, 29];
let i = 0

do{
    if(idades[i] < 0){
        console.log('Comando de saída executado')
        break;
    }
    else if (idades[i] < 18){
        console.log(`${idades[i]} é menor que 18 anos`)
        i++;
        continue;
    }
    else{
        console.log(idades[i]);
    }
    i++;



}while(i < idades.length)