// SPDX-License-Identifier: MIT
pragma solidity ^0.8.0;

import "@openzeppelin/contracts/token/ERC20/ERC20.sol";
import "@openzeppelin/contracts/access/Ownable.sol";

contract EcoToken is ERC20, Ownable {
    uint256 public constant MAX_SUPPLY = 1000000000 * 10**18; // 1 milliard
    
    // Adresse autorisée pour le minting (backend Laravel)
    address public minter;
    
    constructor() ERC20("ECO Token", "ECO") {
        minter = msg.sender;
    }
    
    // Minting réservé au backend
    function mint(address to, uint256 amount) external onlyMinter {
        require(totalSupply() + amount <= MAX_SUPPLY, "Max supply exceeded");
        _mint(to, amount);
    }
    
    // Burning pour ajuster l'offre
    function burn(uint256 amount) external {
        _burn(msg.sender, amount);
    }
    
    modifier onlyMinter() {
        require(msg.sender == minter, "Not authorized");
        _;
    }
    
    function setMinter(address _minter) external onlyOwner {
        minter = _minter;
    }
}
