// SPDX-License-Identifier: MIT
pragma solidity ^0.8.0;

contract PDPDao {
    // Types de propositions
    enum ProposalType {
        PRICE_CHANGE,      // Changement de prix
        ROUTE_ADDITION,    // Ajout d'itinéraire
        ROUTE_MODIFICATION,// Modification d'itinéraire
        PARAMETER_CHANGE   // Changement de paramètres
    }
    
    // Statut des propositions
    enum ProposalStatus {
        PENDING,    // En attente de vote
        ACTIVE,     // Vote en cours
        PASSED,     // Approuvée
        REJECTED,   // Rejetée
        EXECUTED    // Exécutée
    }
    
    struct Proposal {
        uint256 id;
        address proposer;
        ProposalType proposalType;
        string title;
        string description;
        uint256 startTime;
        uint256 endTime;
        uint256 votesFor;
        uint256 votesAgainst;
        uint256 votesAbstain;
        ProposalStatus status;
        mapping(address => bool) hasVoted;
        bytes executionData; // Données pour l'exécution
    }
    
    // Paramètres de gouvernance
    uint256 public quorum; // Quorum minimum (ex: 10% des tokens)
    uint256 public votingPeriod; // Durée du vote (ex: 7 jours)
    uint256 public proposalThreshold; // Minimum de tokens pour proposer
    
    // Référence au contrat token ECO
    // IERC20 public ecoToken; // Commented out to avoid dependency error in this standalone file, assumes IERC20 interface exists or is imported
    address public ecoTokenAddress;

    // Mapping des propositions
    mapping(uint256 => Proposal) public proposals;
    uint256 public proposalCount;
    
    // Événements
    event ProposalCreated(uint256 indexed proposalId, address indexed proposer);
    event VoteCast(uint256 indexed proposalId, address indexed voter, bool support);
    event ProposalExecuted(uint256 indexed proposalId);

    constructor(address _ecoTokenAddress, uint256 _quorum, uint256 _votingPeriod, uint256 _proposalThreshold) {
        ecoTokenAddress = _ecoTokenAddress;
        quorum = _quorum;
        votingPeriod = _votingPeriod;
        proposalThreshold = _proposalThreshold;
    }

    function createProposal(
        ProposalType _type,
        string memory _title,
        string memory _description,
        bytes memory _executionData
    ) external returns (uint256) {
        // Validation logic here (e.g. check token balance)

        proposalCount++;
        Proposal storage newProposal = proposals[proposalCount];
        newProposal.id = proposalCount;
        newProposal.proposer = msg.sender;
        newProposal.proposalType = _type;
        newProposal.title = _title;
        newProposal.description = _description;
        newProposal.startTime = block.timestamp;
        newProposal.endTime = block.timestamp + votingPeriod;
        newProposal.status = ProposalStatus.ACTIVE;
        newProposal.executionData = _executionData;

        emit ProposalCreated(proposalCount, msg.sender);
        return proposalCount;
    }

    function vote(
        uint256 _proposalId,
        bool _support // true = pour, false = contre
    ) external {
        Proposal storage proposal = proposals[_proposalId];
        require(proposal.status == ProposalStatus.ACTIVE, "Proposal is not active");
        require(block.timestamp < proposal.endTime, "Voting period has ended");
        require(!proposal.hasVoted[msg.sender], "Already voted");

        // Logic to check token balance and weight vote
        uint256 weight = 1; // Placeholder: should be ecoToken.balanceOf(msg.sender)

        if (_support) {
            proposal.votesFor += weight;
        } else {
            proposal.votesAgainst += weight;
        }

        proposal.hasVoted[msg.sender] = true;
        emit VoteCast(_proposalId, msg.sender, _support);
    }

    function executeProposal(uint256 _proposalId) external {
        Proposal storage proposal = proposals[_proposalId];
        require(proposal.status == ProposalStatus.ACTIVE, "Proposal is not active");
        require(block.timestamp >= proposal.endTime, "Voting period has not ended");

        if (proposal.votesFor > proposal.votesAgainst && proposal.votesFor >= quorum) {
            proposal.status = ProposalStatus.PASSED;
            // Execute logic
            proposal.status = ProposalStatus.EXECUTED;
            emit ProposalExecuted(_proposalId);
        } else {
            proposal.status = ProposalStatus.REJECTED;
        }
    }

    function getProposal(uint256 _proposalId) external view returns (
        uint256 id,
        address proposer,
        ProposalType proposalType,
        string memory title,
        string memory description,
        uint256 startTime,
        uint256 endTime,
        uint256 votesFor,
        uint256 votesAgainst,
        ProposalStatus status
    ) {
        Proposal storage p = proposals[_proposalId];
        return (
            p.id,
            p.proposer,
            p.proposalType,
            p.title,
            p.description,
            p.startTime,
            p.endTime,
            p.votesFor,
            p.votesAgainst,
            p.status
        );
    }
}
