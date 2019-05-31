"use strict";

/** A class that handles the machining program viewer */

export class MachiningProgramViewer
{
	constructor()
	{
        if (!!MachiningProgramViewer.instance) 
        {
            return MachiningProgramViewer.instance;
        }
        else
        {
            MachiningProgramViewer.instance = this;
            this.goToFirst();
            Object.seal(this);
            return this;
        }
	}

    initialize()
    {
        let elements = document.getElementsByClassName("pannelContainer");
        if(elements.length > 0)
        {
            let viewer = this;
            this.currentPannel = elements[0];
            [...elements].forEach(function(element){
                viewer.hidePannel(element);
            });
        }
        else
        {
            this.currentPannel = null;
        }
    }

	/**
	 * Goes to the first pannel in the collection.
	 * 
	 */
	goToFirst()
	{
        if(typeof(this.currentPannel) === "undefined" || this.currentPannel === null)
        {
            this.initialize() === null
        }

        this.goTo(this.currentPannel.parentElement.firstElementChild)
	}

	/**
	 * Goes to the last pannel in the collection.
	 * 
	 */
	goToLast()
	{
        if(typeof(this.currentPannel) === "undefined" || this.currentPannel === null)
        {
            this.initialize() === null
        }

		this.goTo(this.currentPannel.parentElement.lastElementChild)
	}

	/**
	 * Goes to the previous pannel in the collection.
	 * 
	 */
	goToPrevious()
	{
        if(typeof(this.currentPannel) === "undefined" || this.currentPannel === null)
        {
            this.initialize() === null
        }

		this.goTo(this.currentPannel.previousElementSibling);
	}

	/**
	 * Goes to the next pannel in the collection
	 * 
	 */
	goToNext()
	{
        if(typeof(this.currentPannel) === "undefined" || this.currentPannel === null)
        {
            this.initialize() === null
        }

		this.goTo(this.currentPannel.nextElementSibling);
	}

	/**
	 * Goes to the specified index in the collection
	 * @param {Node} newPannel The new pannel to display 
	 */
	goTo(newPannel)
	{
        if(typeof(this.currentPannel) === "undefined" || this.currentPannel === null)
        {
            this.initialize();
            if(this.currentPannel == null)
            {
                return;
            }
        }
        else if(newPannel === null)
        {
            return;
        }

        this.hidePannel(this.currentPannel);
        this.showPannel(newPannel);
        this.currentPannel = newPannel;
	}

	/**
	 * Prints the current pannel
	 * 
	 */
	printPannel()
	{
		window.print();
	}

	/**
	 * Prints all pannels
	 * 
	 */
	printAllPannels()
	{
        let viewer = this;
		let displayStatusArray = [...document.getElementsByClassName("pannelContainer")].map(function(element){
            let backedUpState = element.style.display;
            viewer.showPannel(element);
            return backedUpState;
        });
		window.print();
		displayStatusArray.forEach(function(status, index){
            if(status === "none")
            {
                viewer.hidePannel(document.getElementsByClassName("pannelContainer")[index]);
            }
        });
    }
    
    /**
     * Shows the specified pannel
     * 
     */
    showPannel(pannel)
    {
        pannel.style.display = "block";
    }

    /**
     * Hides the specified pannel
     * 
     */
    hidePannel(pannel)
    {
        pannel.style.display = "none";
    }

    /**
     * Shows all pannels (used for printing)
     * 
     */
    showAllPannels()
    {
        [...document.getElementsByClassName("pannelContainer")].forEach(function(pannel){
            showPannel(pannel);
        });
    }

    /**
     * Hides all pannels (used for printing)
     * 
     */
    hideAllPannels()
    {
        [...document.getElementsByClassName("pannelContainer")].forEach(function(pannel){
            hidePannel(pannel);
        });
    }
}