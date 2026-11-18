
async function carregarFrota() {
    try {
        const resposta = await fetch('backend/bikes.php');
        const bikes = await resposta.json();
        
        console.log("Frota carregada:", bikes);
            } catch (error) {
        console.error("Erro ao buscar frota:", error);
    }
}

carregarFrota();

async function alugarBike(bikeId, nomeUsuario) {
    const resposta = await fetch('backend/alugueis.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            acao: 'alugar',
            bike_id: bikeId,
            usuario_nome: nomeUsuario
        })
    });
    const resultado = await resposta.json();
    console.log(resultado);
}

async function devolverBike(aluguelId) {
    const resposta = await fetch('backend/alugueis.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            acao: 'devolver',
            aluguel_id: aluguelId
        })
    });
    const resultado = await resposta.json();
    console.log(resultado);
}
async function cadastrarBike(event) {
    event.preventDefault(); 

    const formElement = document.getElementById('seu-formulario-id');
    const formData = new FormData(formElement); 
   

    try {
        const resposta = await fetch('backend/bikes.php', {
            method: 'POST',
            body: formData 
           
        });

        const resultado = await resposta.json();
        
        if (resposta.ok) {
            alert(resultado.mensagem);
            
        } else {
            alert(resultado.erro);
        }
    } catch (error) {
        console.error("Erro ao enviar:", error);
    }
}